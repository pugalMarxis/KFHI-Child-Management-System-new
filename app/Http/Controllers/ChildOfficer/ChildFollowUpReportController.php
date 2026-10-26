<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChildFollowUpReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ChildFollowUpReportController extends Controller
{
    private function reportQuery(Request $request)
    {
        $query = FollowUp::with(['child', 'assignedTo']);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('action_taken', 'like', "%{$search}%")
                    ->orWhereHas('child', function ($childQuery) use ($search) {
                        $childQuery->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('next_follow_up_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('next_follow_up_date', '<=', $request->date_to);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $followUps = $this->reportQuery($request)
            ->orderByDesc('next_follow_up_date')
            ->paginate(15)
            ->withQueryString();

        $baseQuery = FollowUp::query();

        $total = (clone $baseQuery)->count();

        $pending = (clone $baseQuery)
            ->where('status', 'pending')
            ->count();

        $completed = (clone $baseQuery)
            ->where('status', 'completed')
            ->count();

        $overdue = (clone $baseQuery)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<', today())
            ->count();

        $statuses = FollowUp::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $priorities = FollowUp::query()
            ->whereNotNull('priority')
            ->distinct()
            ->orderBy('priority')
            ->pluck('priority');

        return view('child_officer.follow-up-reports.children', compact(
            'followUps',
            'total',
            'pending',
            'completed',
            'overdue',
            'statuses',
            'priorities'
        ));
    }

    public function exportExcel(Request $request)
    {
        $followUps = $this->reportQuery($request)
            ->orderByDesc('next_follow_up_date')
            ->get();

        return Excel::download(
            new ChildFollowUpReportExport($followUps),
            'child-follow-up-report.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $followUps = $this->reportQuery($request)
            ->orderByDesc('next_follow_up_date')
            ->get();

        $pdf = Pdf::loadView(
            'child_officer.follow-up-reports.children-pdf',
            compact('followUps')
        )->setPaper('a4', 'landscape');

        return $pdf->download('child-follow-up-report.pdf');
    }
}
