<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function index(Request $request): View
    {
        $query = Child::query();
    
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
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $children = $query->latest()->get();
    
        return view('admin.children.index', compact('children'));
    }
    public function create(): View
    {
        return view('admin.children.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_code' => [
                'required',
                'string',
                'max:50',
                'unique:children,child_code',
            ],
    
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
    
            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],
    
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
    
            'date_of_birth' => [
                'required',
                'date',
            ],
    
            'gender' => [
                'required',
                'in:male,female,other',
            ],
    
            'school_name' => [
                'nullable',
                'string',
                'max:255',
            ],
    
            'grade' => [
                'nullable',
                'string',
                'max:50',
            ],
    
            'address' => [
                'nullable',
                'string',
            ],
    
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],
    
            'notes' => [
                'nullable',
                'string',
            ],
            'profile_photo' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:2048',
],
        ]);
        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')
                ->store('children', 'public');
        }
        $child = Child::create([
            'child_code' => $validated['child_code'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'school_name' => $validated['school_name'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'address' => $validated['address'] ?? null,
            'district' => $validated['district'] ?? null,
            'gn_division' => $validated['gn_division'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
            'created_by' => auth()->id(),
            'profile_photo' => $profilePhotoPath,
        ]);

        app(\App\Services\QRCodeService::class)->generateForChild($child);
    
        return redirect()
            ->route('children.show', $child)
            ->with('success', 'Child registered successfully. The permanent QR code is ready.');
    }

    public function show(Child $child): View
    {
        $child->load('guardians');
    
        return view('admin.children.show', compact('child'));
    }

    public function edit(Child $child): View
    {
        return view('admin.children.edit', compact('child'));
    }

    public function update(Request $request, Child $child): RedirectResponse
{
    $validated = $request->validate([
        'first_name' => [
            'required',
            'string',
            'max:100',
        ],

        'middle_name' => [
            'nullable',
            'string',
            'max:100',
        ],

        'last_name' => [
            'required',
            'string',
            'max:100',
        ],

        'date_of_birth' => [
            'required',
            'date',
        ],

        'gender' => [
            'required',
            'in:male,female,other',
        ],

        'school_name' => [
            'nullable',
            'string',
            'max:255',
        ],

        'grade' => [
            'nullable',
            'string',
            'max:50',
        ],

        'district' => [
            'nullable',
            'string',
            'max:100',
        ],
        'gn_division' => [
    'nullable',
    'string',
    'max:150',
],

        'address' => [
            'nullable',
            'string',
        ],

        'notes' => [
            'nullable',
            'string',
        ],

        'status' => [
            'required',
            'in:active,inactive',
        ],

        'profile_photo' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ],
    ]);

    $child->first_name = $validated['first_name'];
    $child->middle_name = $validated['middle_name'] ?? null;
    $child->last_name = $validated['last_name'];
    $child->date_of_birth = $validated['date_of_birth'];
    $child->gender = $validated['gender'];
    $child->school_name = $validated['school_name'] ?? null;
    $child->grade = $validated['grade'] ?? null;
    $child->district = $validated['district'] ?? null;
    $child->gn_division = $validated['gn_division'] ?? null;
    $child->address = $validated['address'] ?? null;
    $child->notes = $validated['notes'] ?? null;
    $child->status = $validated['status'];
    $child->profile_version = ((int) $child->profile_version) + 1;

    if ($request->hasFile('profile_photo')) {

        // Delete the old profile photo
        if ($child->profile_photo) {
            Storage::disk('public')->delete($child->profile_photo);
        }
    
        // Store the new profile photo
        $child->profile_photo = $request->file('profile_photo')
            ->store('children', 'public');
    }

    $child->save();

    return redirect()
        ->route('children.show', $child)
        ->with('success', 'Child information updated successfully.');
}
    public function destroy(Child $child): RedirectResponse
    {
        $child->forceDelete();
    
        return redirect()
            ->route('children.index')
            ->with('success', 'Child record permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

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
                $child->forceDelete();
            }

            return $children->count();
        });

        if ($deleted === null) {
            return back()->withErrors(['bulk_delete' => 'No children were deleted. Refresh the page and try again.']);
        }

        return back()->with('success', "Permanently deleted {$deleted} selected child record(s).");
    }
}
