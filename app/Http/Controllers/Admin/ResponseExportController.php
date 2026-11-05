<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FilteredResponseExport;
use App\Http\Controllers\Controller;
use App\Models\QuestionnaireResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponseExportController extends Controller
{
    public function export(Request $request): BinaryFileResponse
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);

        $exportType = $request->input('export_type', 'full');
        abort_unless(in_array($exportType, ['full', 'child'], true), 400);

        $query = QuestionnaireResponse::query()->with([
            'questionnaire.event',
            'child',
            'enumerator',
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
