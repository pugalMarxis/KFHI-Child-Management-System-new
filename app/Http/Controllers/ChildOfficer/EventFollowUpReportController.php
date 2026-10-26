<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\EventFollowUp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventFollowUpReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class EventFollowUpReportController extends Controller
{
    private function reportQuery(Request $request)
    {
        $query = EventFollowUp::with(['event', 'assignee', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('event_code', 'like', "%{$search}%");
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
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $followUps = $this->reportQuery($request)
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        $total = EventFollowUp::count();

        $pending = EventFollowUp::whereIn(
            'status',
            ['open', 'pending', 'in_progress']
        )->count();

        $completed = EventFollowUp::where('status', 'completed')->count();

        $overdue = EventFollowUp::whereNotIn(
            'status',
            ['completed', 'cancelled']
        )
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        $statuses = EventFollowUp::whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $priorities = EventFollowUp::whereNotNull('priority')
            ->distinct()
            ->orderBy('priority')
            ->pluck('priority');

        return view('child_officer.follow-up-reports.events', compact(
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
            ->orderByDesc('due_date')
            ->get();

        return Excel::download(
            new EventFollowUpReportExport($followUps),
            'event-follow-up-report.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $followUps = $this->reportQuery($request)
            ->orderByDesc('due_date')
            ->get();

        $pdf = Pdf::loadView(
            'child_officer.follow-up-reports.events-pdf',
            compact('followUps')
        )->setPaper('a4', 'landscape');

        return $pdf->download('event-follow-up-report.pdf');
    }
}
