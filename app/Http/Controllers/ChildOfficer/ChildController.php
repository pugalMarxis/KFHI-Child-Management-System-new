<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display all registered children.
     */
    public function index(Request $request)
    {
        $query = Child::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('child_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // District filter
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $children = $query
            ->latest()
            ->get();

        $districts = Child::query()
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        return view('child_officer.children.index', compact(
            'children',
            'districts'
        ));
    }

    /**
     * Display a specific child.
     */
    public function show(Child $child)
    {
        $child->load([
            'guardians',
            'qrCode',
            'digitalId',
            'additionalInformation.addedBy',
        ]);
    
        return view(
            'child_officer.children.show',
            compact('child')
        );
    }

    /**
     * Show the child registration form.
     */
    public function create()
    {
        return view('child_officer.children.create');
    }

    /**
     * Store a newly registered child.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_code' => 'required|string|max:100|unique:children,child_code',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:20',
            'school_name' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'gn_division' => 'nullable|string|max:150',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] =
                $request->file('profile_photo')->store(
                    'children',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Created By
        |--------------------------------------------------------------------------
        */
        $validated['created_by'] = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | Dynamic Custom Fields Handling
        |--------------------------------------------------------------------------
        */
        $customFields = [];
        if ($request->has('dynamic_fields')) {
            foreach ($request->input('dynamic_fields') as $key => $value) {
                if (!empty($key)) {
                    $customFields[$key] = $value;
                }
            }
        }
        $validated['custom_fields'] = $customFields;

        /*
        |--------------------------------------------------------------------------
        | Create Child
        |--------------------------------------------------------------------------
        */
        $child = Child::create($validated);
        app(\App\Services\QRCodeService::class)->generateForChild($child);

        return redirect()
            ->route('child_officer.children.index')
            ->with('success', 'Child registered successfully with custom fields.');
    }

    /**
     * Show the child edit form.
     */
    public function edit(Child $child)
    {
        return view('child_officer.children.edit', compact('child'));
    }

    /**
     * Update the child information.
     */
    public function update(Request $request, Child $child)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:20',
            'school_name' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'gn_division' => 'nullable|string|max:150',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] =
                $request->file('profile_photo')->store(
                    'children',
                    'public'
                );
        }

        // Handle dynamic fields on update, including when all fields are removed.
        if ($request->has('custom_fields_present') || $request->has('dynamic_fields')) {
            $customFields = [];
            foreach ($request->input('dynamic_fields', []) as $key => $value) {
                if (!empty(trim((string) $key))) {
                    $customFields[trim((string) $key)] = $value;
                }
            }
            $validated['custom_fields'] = $customFields;
        }

        $child->profile_version = ((int) $child->profile_version) + 1;
        $child->update($validated);

        return redirect()
            ->route('child_officer.children.show', $child)
            ->with('success', 'Child information updated successfully.');
    }

    public function destroy(Child $child)
    {
        // Deactivate the child's active QR code
        if ($child->qrCode) {
            $child->qrCode->update([
                'status' => 'inactive',
            ]);
        }

        // Soft delete the child
        $child->delete();

        return redirect()
            ->route('child_officer.children.index')
            ->with('success', 'Child information deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless($request->user()?->role === 'child_officer', 403);

        $validated = $request->validate([
            'child_ids' => ['required', 'array', 'min:1', 'max:500'],
            'child_ids.*' => ['required', 'integer', 'distinct', 'exists:children,id'],
        ]);

        $deleted = DB::transaction(function () use ($validated) {
            $children = Child::query()->whereIn('id', $validated['child_ids'])->lockForUpdate()->get();
            if ($children->count() !== count($validated['child_ids'])) {
                return null;
            }

            foreach ($children as $child) {
                if ($child->qrCode) {
                    $child->qrCode->update(['status' => 'inactive']);
                }
                $child->delete();
            }

            return $children->count();
        });

        if ($deleted === null) {
            return back()->withErrors(['bulk_delete' => 'No children were deleted. Refresh the page and try again.']);
        }

        return back()->with('success', "Deleted {$deleted} selected child record(s).");
    }
}
