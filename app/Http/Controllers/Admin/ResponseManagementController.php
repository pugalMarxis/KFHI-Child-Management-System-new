<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enumerator;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Services\ChildRecordMergeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResponseManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeReviewer($request);

        $query = QuestionnaireResponse::with([
            'questionnaire.event',
            'child',
            'enumerator',
            'reviewer',
            'merger',
        ]);

        if ($request->filled('questionnaire_id')) {
            $query->where('questionnaire_id', $request->input('questionnaire_id'));
        }

        if ($request->filled('event_id')) {
            $query->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('event_id', $request->input('event_id'));
            });
        }

        if ($request->filled('enumerator_id')) {
            $query->where('enumerator_id', $request->input('enumerator_id'));
        }

        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        }

        if ($request->filled('merge_status')) {
            $query->where('merge_status', $request->input('merge_status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('submitted_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('submitted_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('primary_identifier_value', 'like', "%{$search}%")
                    ->orWhere('enumerator_name', 'like', "%{$search}%")
                    ->orWhereHas('child', function ($cq) use ($search) {
                        $cq->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('questionnaire', function ($qq) use ($search) {
                        $qq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $responses = $query->latest('submitted_at')->paginate(15)->withQueryString();

        // Database statistics
        $stats = [
            'total' => QuestionnaireResponse::count(),
            'awaiting_review' => QuestionnaireResponse::whereIn('review_status', ['submitted', 'under_review'])->count(),
            'approved' => QuestionnaireResponse::where('review_status', 'approved')->count(),
            'merged' => QuestionnaireResponse::where('merge_status', 'merged')->count(),
            'rejected' => QuestionnaireResponse::where('review_status', 'rejected')->count(),
            'returned' => QuestionnaireResponse::where('review_status', 'returned')->count(),
        ];

        // Enumerator productivity statistics
        $enumeratorStats = Enumerator::withCount([
            'responses as total_submissions',
            'responses as today_submissions' => function ($q) {
                $q->whereDate('submitted_at', today());
            },
            'responses as pending_submissions' => function ($q) {
                $q->whereIn('review_status', ['submitted', 'under_review']);
            },
            'responses as approved_submissions' => function ($q) {
                $q->where('review_status', 'approved');
            },
        ])
        ->withMin('responses as first_activity', 'submitted_at')
        ->withMax('responses as latest_activity', 'submitted_at')
        ->get();

        $questionnaires = Questionnaire::orderBy('title')->get();
        $events = Event::orderBy('title')->get();
        $enumerators = Enumerator::orderBy('name')->get();

        return view('admin.responses.index', compact(
            'responses',
            'stats',
            'enumeratorStats',
            'questionnaires',
            'events',
            'enumerators'
        ));
    }

    public function show(
        Request $request,
        QuestionnaireResponse $response,
        ChildRecordMergeService $mergeService
    ): View {
        $this->authorizeReviewer($request);

        $response->load([
            'questionnaire.questions.options',
            'child',
            'answers.question',
            'enumerator',
            'reviewer',
            'merger',
        ]);

        $comparison = $mergeService->buildComparison($response);

        // Previous submissions for this child if linked
        $previousSubmissions = collect();
        if ($response->child_id) {
            $previousSubmissions = QuestionnaireResponse::where('child_id', $response->child_id)
                ->where('id', '!=', $response->id)
                ->with('questionnaire')
                ->latest('submitted_at')
                ->limit(5)
                ->get();
        }

        return view('admin.responses.review', compact('response', 'comparison', 'previousSubmissions'));
    }

    public function updateStatus(
        Request $request,
        QuestionnaireResponse $response
    ): RedirectResponse {
        $this->authorizeReviewer($request);

        $validated = $request->validate([
            'review_status' => ['required', 'in:under_review,approved,rejected,returned'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $response->update([
            'review_status' => $validated['review_status'],
            'review_notes' => $validated['review_notes'] ?? $response->review_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.responses.show', $response)
            ->with('success', "Response status updated to " . ucfirst(str_replace('_', ' ', $validated['review_status'])) . ".");
    }

    public function merge(
        Request $request,
        QuestionnaireResponse $response,
        ChildRecordMergeService $mergeService
    ): RedirectResponse {
        $this->authorizeReviewer($request);

        $validated = $request->validate([
            'approved_fields' => ['required', 'array'],
            'approved_fields.*' => ['string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $child = $mergeService->mergeApprovedData(
                $response,
                $request->user(),
                $validated['approved_fields'],
                $validated['notes'] ?? null,
                $request->ip(),
                $request->userAgent()
            );

            return redirect()
                ->route('admin.responses.show', $response)
                ->with('success', "Approved fields merged successfully into child record ({$child->child_code}). Audit log created.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['merge' => $e->getMessage()]);
        }
    }
    public function destroy(
                Request $request,
                QuestionnaireResponse $response
            ): RedirectResponse {
        $this->authorizeReviewer($request);

        if ($response->merge_status === 'merged') {
            return back()->withErrors([
                'delete' => 'This response has already been merged into a child record. It cannot be deleted here.',
            ]);
        }

        $response->delete();

        $route = $request->user()->role === 'child_officer'
            ? 'child_officer.responses.index'
            : 'admin.responses.index';

        return redirect()
            ->route($route)
            ->with('success', 'Response and its answers were permanently deleted.');
    }

    private function authorizeReviewer(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);
    }
}
