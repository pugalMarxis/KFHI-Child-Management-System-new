<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventAttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\QRCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EventAttendanceController extends Controller
{
    public function index(Event $event): View
    {
        $attendances = Attendance::with(['child', 'markedBy', 'markedByEnumerator'])
            ->where('event_id', $event->id)
            ->latest('attendance_time')
            ->get();

        return view(
            'admin.events.attendance.index',
            compact('event', 'attendances')
        );
    }

    public function export(Request $request, Event $event): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless(
            $user && $user->isActive() && ($user->isAdmin() || $user->isChildOfficer()),
            403
        );

        $attendances = Attendance::with(['child', 'markedBy', 'markedByEnumerator'])
            ->where('event_id', $event->id)
            ->orderBy('attendance_time')
            ->get();

        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $event->event_code ?: (string) $event->id);
        $filename = 'attendance_' . $safeCode . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new EventAttendanceExport($attendances), $filename);
    }

    public function scan(
        Request $request,
        Event $event
    ): JsonResponse {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $tokenHash = hash('sha256', $request->token);

        $qrCode = QRCode::with('child')
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
            ->first();

        // New permanent-code QR cards work directly with this online endpoint too.
        $child = $qrCode?->child ?? \App\Models\Child::where('child_code', $request->token)->first();

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive QR code.',
            ], 404);
        }

        if ($child->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This child record is inactive.',
            ], 422);
        }

        $alreadyMarked = Attendance::where('event_id', $event->id)
            ->where('child_id', $child->id)
            ->exists();

        if ($alreadyMarked) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance has already been marked for this child.',
                'child' => [
                    'child_code' => $child->child_code,
                    'name' => trim(
                        $child->first_name . ' ' .
                        $child->middle_name . ' ' .
                        $child->last_name
                    ),
                ],
            ], 409);
        }

        Attendance::create([
            'child_id' => $child->id,
            'event_id' => $event->id,
            'marked_by' => auth()->id(),
            'attendance_time' => now(),
            'method' => 'qr',
            'status' => 'present',
        ]);

        $qrCode?->update(['last_scanned_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully.',
            'child' => [
                'child_code' => $child->child_code,
                'name' => trim(
                    $child->first_name . ' ' .
                    $child->middle_name . ' ' .
                    $child->last_name
                ),
            ],
        ]);
    }
}
