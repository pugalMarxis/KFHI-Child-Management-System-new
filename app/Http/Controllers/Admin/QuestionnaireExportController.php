<?php

namespace App\Http\Controllers\Admin;

use App\Exports\QuestionnaireResponsesExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionnaireExportController extends Controller
{
    public function export(Request $request, Event $event, Questionnaire $questionnaire): BinaryFileResponse
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'child_officer'], true), 403);

        $query = $questionnaire->responses()
            ->with(['child', 'answers.question', 'enumerator']);

        if ($request->filled('review_status') && $request->input('review_status') !== 'all') {
            $query->where('review_status', $request->input('review_status'));
        }

        if ($request->filled('merge_status') && $request->input('merge_status') !== 'all') {
            $query->where('merge_status', $request->input('merge_status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('submitted_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('submitted_at', '<=', $request->input('end_date'));
        }

        $responses = $query->latest('submitted_at')->get();

        $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($questionnaire->title));
        $filename = "questionnaire_{$safeTitle}_responses_" . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new QuestionnaireResponsesExport($questionnaire, $responses),
            $filename
        );
    }
}
