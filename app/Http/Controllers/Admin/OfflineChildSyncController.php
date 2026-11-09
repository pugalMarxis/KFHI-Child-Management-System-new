<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enumerator;
use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OfflineChildSyncController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStaff($request);

        $districts = Child::query()
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        $divisions = Child::query()
            ->whereNotNull('gn_division')
            ->where('gn_division', '!=', '')
            ->distinct()
            ->orderBy('gn_division')
            ->pluck('gn_division');

        $enumerators = Enumerator::query()
            ->where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'enumerator_code']);

        return view('admin.child_sync.index', compact('districts', 'divisions', 'enumerators'));
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $filters = $request->validate([
            'district' => ['nullable', 'string', 'max:100'],
            'gn_division' => ['nullable', 'string', 'max:150'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $children = $this->filteredChildren($filters)
            ->select([
                'id', 'child_code', 'first_name', 'middle_name', 'last_name',
                'district', 'gn_division', 'status',
            ])
            ->orderBy('id')
            ->paginate(30);

        return response()->json([
            'data' => $children->getCollection()->map(fn (Child $child) => [
                'id' => $child->id,
                'child_code' => $child->child_code,
                'name' => $this->fullName($child),
                'district' => $child->district,
                'gn_division' => $child->gn_division,
                'status' => $child->status,
            ]),
            'current_page' => $children->currentPage(),
            'last_page' => $children->lastPage(),
            'total' => $children->total(),
        ]);
    }

    public function photo(Request $request, Child $child)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        abort_unless(
            $child->profile_photo && Storage::disk('public')->exists($child->profile_photo),
            404,
            'This child has no saved profile photo.'
        );

        return Storage::disk('public')->response(
            $child->profile_photo,
            null,
            ['Cache-Control' => 'private, no-store']
        );
    }

    public function download(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'all' => ['required', 'boolean'],
            'child_ids' => ['nullable', 'array', 'max:100'],
            'child_ids.*' => ['integer', 'distinct', 'exists:children,id'],
            'district' => ['nullable', 'string', 'max:100'],
            'gn_division' => ['nullable', 'string', 'max:150'],
            'search' => ['nullable', 'string', 'max:100'],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'include_family_details' => ['nullable', 'boolean'],
            'enumerator_ids' => ['nullable', 'array', 'max:500'],
            'enumerator_ids.*' => ['integer', 'distinct', 'exists:enumerators,id'],
            'allow_all_enumerators' => ['nullable', 'boolean'],

        ]);

        $enumerators = collect();
        if ($validated['allow_all_enumerators'] ?? false) {
            $enumerators = Enumerator::query()
                ->where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->where('status', 'active')
                ->get();
        } elseif (!empty($validated['enumerator_ids'])) {
            $enumerators = Enumerator::query()
                ->where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->where('status', 'active')
                ->whereIn('id', $validated['enumerator_ids'])
                ->get();

            abort_if(
                $enumerators->count() !== count($validated['enumerator_ids']),
                422,
                'One or more selected enumerators are inactive or unavailable.'
            );
        }

        $query = $this->filteredChildren($validated);

        if ($validated['all']) {
            $query->where('id', '>', $validated['after_id'] ?? 0)
                ->orderBy('id')
                ->limit(100);
        } else {
            $ids = $validated['child_ids'] ?? [];
            abort_if($ids === [], 422, 'Choose at least one child to download.');
            $query->whereIn('id', $ids);
        }

        $query->with(['guardians' => function ($guardianQuery) {
            $guardianQuery->select([
                'id', 'child_id', 'full_name', 'relationship', 'phone', 'is_primary',
            ]);
        }]);

        $query->with(['qrCodes' => function ($qrQuery) {
            $qrQuery->where('qr_codes.status', 'active')
                ->select(['qr_codes.child_id', 'qr_codes.token_hash']);
        }]);

        $children = $query->get();


        if ($enumerators->isNotEmpty() && $children->isNotEmpty()) {
            DB::transaction(function () use ($enumerators, $children, $request, $validated) {
                foreach ($enumerators as $enumerator) {
                    $assignments = $children->mapWithKeys(fn (Child $child) => [
                        $child->id => [
                            'allowed_by' => $request->user()->id,
                            'include_family_details' => (bool) ($validated['include_family_details'] ?? false),
                        ],
                    ])->all();

                    $enumerator->allowedChildren()->syncWithoutDetaching($assignments);
                    foreach ($assignments as $childId => $pivotData) {
                        $enumerator->allowedChildren()->updateExistingPivot($childId, $pivotData);
                    }
                }
            });
        }
        
        $includeFamilyDetails = (bool) ($validated['include_family_details'] ?? false);

        $records = $children->map(function (Child $child) use ($includeFamilyDetails) {
            $record = [
                'id' => $child->id,
                'qr_token_hashes' => $child->qrCodes->pluck('token_hash')->values(),
                'child_code' => $child->child_code,
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'full_name' => $this->fullName($child),
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'gender' => $child->gender,
                'school_name' => $child->school_name,
                'grade' => $child->grade,
                'address' => $child->address,
                'district' => $child->district,
                'gn_division' => $child->gn_division,
                'status' => $child->status,
                'profile_version' => (int) $child->profile_version,
                'profile_photo_url' => $child->profile_photo ? asset('storage/' . $child->profile_photo) : null,
                'updated_at' => $child->updated_at?->toIso8601String(),
            ];

            if ($includeFamilyDetails) {
                $record['guardians'] = $child->guardians
                    ->map(fn ($guardian) => [
                        'full_name' => $guardian->full_name,
                        'relationship' => $guardian->relationship,
                        'phone' => $guardian->phone,
                        'is_primary' => (bool) $guardian->is_primary,
                    ])
                    ->values();
            }

            return $record;
        })->values();

        $lastRecord = $children->last();

        return response()->json([
            'data' => $records,
            'next_after_id' => $validated['all'] && $children->count() === 100
                ? $lastRecord?->id
                : null,
            'count' => $records->count(),
        ]);
    }

    public function downloadAssignedForEnumerator(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $enumerator = Enumerator::query()->find(session('verified_enumerator_id'));
        if (!$enumerator || !$enumerator->isActive()) {
            return response()->json([
                'message' => 'Enumerator access has expired. Sign in again before syncing.',
            ], 401);
        }

        $afterId = $validated['after_id'] ?? 0;
        $authorizedChildIds = $afterId === 0
            ? $enumerator->allowedChildren()->pluck('children.id')->all()
            : null;

        $children = $enumerator->allowedChildren()
            ->where('children.id', '>', $afterId)
            ->with(['guardians' => function ($guardianQuery) {
                $guardianQuery->select([
                    'guardians.id', 'guardians.child_id', 'guardians.full_name',
                    'guardians.relationship', 'guardians.phone', 'guardians.is_primary',
                ]);
            }])
            ->with(['qrCodes' => function ($qrQuery) {
                $qrQuery->where('qr_codes.status', 'active')
                    ->select(['qr_codes.child_id', 'qr_codes.token_hash']);
            }])
            ->orderBy('children.id')
            ->limit(100)
            ->get();

        $records = $children->map(function (Child $child) use ($enumerator) {
            $record = [
                'id' => $child->id,
                'child_code' => $child->child_code,
                'qr_token_hashes' => $child->qrCodes->pluck('token_hash')->values(),
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'full_name' => $this->fullName($child),
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'gender' => $child->gender,
                'school_name' => $child->school_name,
                'grade' => $child->grade,
                'address' => $child->address,
                'district' => $child->district,
                'gn_division' => $child->gn_division,
                'status' => $child->status,
                'profile_version' => (int) $child->profile_version,
                'profile_photo_url' => $child->profile_photo ? asset('storage/' . $child->profile_photo) : null,
                'updated_at' => $child->updated_at?->toIso8601String(),
                'allowed_enumerator_ids' => [$enumerator->id],
            ];

            if ((bool) $child->pivot->include_family_details) {
                $record['guardians'] = $child->guardians->map(fn ($guardian) => [
                    'full_name' => $guardian->full_name,
                    'relationship' => $guardian->relationship,
                    'phone' => $guardian->phone,
                    'is_primary' => (bool) $guardian->is_primary,
                ])->values();
            }

            return $record;
        })->values();

        $lastChild = $children->last();

        return response()->json([
            'data' => $records,
            'next_after_id' => $children->count() === 100 ? $lastChild?->id : null,
            'count' => $records->count(),
            'authorized_child_ids' => $authorizedChildIds,
        ]);
    }

    private function filteredChildren(array $filters)
    {
        $query = Child::query();

        if (!empty($filters['district'])) {
            $query->where('district', $filters['district']);
        }

        if (!empty($filters['gn_division'])) {
            $query->where('gn_division', $filters['gn_division']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($builder) use ($search) {
                $builder->where('child_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function fullName(Child $child): string
    {
        return trim(implode(' ', array_filter([
            $child->first_name,
            $child->middle_name,
            $child->last_name,
        ], fn ($part) => filled($part))));
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(
            in_array($request->user()?->role, ['admin', 'child_officer'], true),
            403
        );
    }
}
