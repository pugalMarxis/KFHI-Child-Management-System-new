<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FilteredResponseExport;
use App\Exports\EnumeratorActivityExport;
use App\Http\Controllers\Controller;
use App\Models\Enumerator;
use App\Models\QuestionnaireResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponseExportController extends Controller
{
    public function exportEnumerators(Request $request): BinaryFileResponse
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);

        $validated = $request->validate([
            'enumerator_ids' => ['required', 'array', 'min:1', 'max:500'],
            'enumerator_ids.*' => ['required', 'integer', 'distinct', 'exists:enumerators,id'],
        ]);

        $enumerators = Enumerator::query()
            ->whereIn('id', $validated['enumerator_ids'])
            ->withCount('questionnaires')
            ->withCount([
                'responses as total_submissions',
                'responses as today_submissions' => fn ($query) => $query->whereDate('submitted_at', today()),
                'responses as pending_submissions' => fn ($query) => $query->whereIn('review_status', ['submitted', 'under_review']),
                'responses as approved_submissions' => fn ($query) => $query->where('review_status', 'approved'),
            ])
            ->withMin('responses as first_activity', 'submitted_at')
            ->withMax('responses as latest_activity', 'submitted_at')
            ->orderBy('name')
            ->get();

        return Excel::download(
            new EnumeratorActivityExport($enumerators),
            'selected_enumerator_activity_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function export(Request $request): BinaryFileResponse
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);

        $dateRules = [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'response_ids' => ['sometimes', 'array', 'min:1', 'max:500'],
            'response_ids.*' => ['required', 'integer', 'distinct', 'exists:questionnaire_responses,id'],
        ];
        if ($request->filled('start_date')) {
            $dateRules['end_date'][] = 'after_or_equal:start_date';
        }
        $request->validate($dateRules);

        $exportType = $request->input('export_type', 'full');
        abort_unless(in_array($exportType, ['full', 'child'], true), 400);

        $query = QuestionnaireResponse::query()->with([
            'questionnaire.event',
            'child',
            'enumerator',
        ]);

        if ($request->filled('response_ids')) {
            $query->whereIn('id', $request->input('response_ids'));
        }

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

        $responses = $query->latest('submitted_at')->get();

        $filename = $exportType === 'child'
            ? 'filtered_child_data_' . now()->format('Ymd_His') . '.xlsx'
            : 'filtered_response_data_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new FilteredResponseExport($responses, $exportType === 'child'),
            $filename
        );
    }
}
