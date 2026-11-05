<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enumerator;
use App\Models\Questionnaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EnumeratorManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStaff($request);

        $query = Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
            ->with(['creator', 'questionnaires'])
            ->withCount('responses');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('enumerator_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $enumerators = $query->latest()->paginate(15)->withQueryString();

        $publishedQuestionnaires = Questionnaire::where('status', 'published')
            ->orderBy('title')
            ->get();

        $stats = [
            'total' => Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')->count(),
            'active' => Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')->where('status', 'active')->count(),
            'inactive' => Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')->where('status', 'inactive')->count(),
        ];

       

        return view('admin.enumerators.index', compact('enumerators', 'publishedQuestionnaires', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pin_code' => ['required', 'string', 'min:4', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
            'questionnaire_ids' => ['nullable', 'array'],
            'questionnaire_ids.*' => ['integer', 'exists:questionnaires,id'],
        ]);

        // Auto-generate next Enumerator Code e.g. ENUM-001, ENUM-002
        $lastId = Enumerator::max('id') ?? 0;
        $code = 'ENUM-' . str_pad((string) ($lastId + 1), 3, '0', STR_PAD_LEFT);

        $enumerator = Enumerator::create([
            'enumerator_code' => $code,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'pin_code' => $validated['pin_code'],
            'status' => $validated['status'],
            'created_by' => $request->user()?->id,
        ]);

        if (!empty($validated['questionnaire_ids'])) {
            $enumerator->questionnaires()->sync($validated['questionnaire_ids']);
        }

        return redirect()
            ->route('admin.enumerators.index')
            ->with('success', "Enumerator {$enumerator->name} created successfully with ID: {$enumerator->enumerator_code}.");
    }

    public function update(Request $request, Enumerator $enumerator): RedirectResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pin_code' => ['nullable', 'string', 'min:4', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
            'questionnaire_ids' => ['nullable', 'array'],
            'questionnaire_ids.*' => ['integer', 'exists:questionnaires,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['pin_code'])) {
            $updateData['pin_code'] = $validated['pin_code'];
        }

        $enumerator->update($updateData);

        if ($request->has('questionnaire_ids')) {
            $enumerator->questionnaires()->sync($validated['questionnaire_ids'] ?? []);
        }

        return redirect()
            ->route('admin.enumerators.index')
            ->with('success', "Enumerator {$enumerator->enumerator_code} updated successfully.");
    }

    public function destroy(Request $request, Enumerator $enumerator): RedirectResponse
    {
        $this->authorizeStaff($request);

        $enumerator->delete();

        
        $route = $request->user()->role === 'child_officer'
            ? 'child_officer.enumerators.index'
            : 'admin.enumerators.index';

        return redirect()
            ->route($route)
            ->with('success', 'Enumerator record removed. Submitted responses were kept.');
                    
            }

    public function updateCommonPassword(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'common_password' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        Enumerator::setCommonPassword($validated['common_password']);

        return back()->with('success', 'Common Enumerator Access Password updated successfully.');
    }

    private function authorizeStaff(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);
    }
}
