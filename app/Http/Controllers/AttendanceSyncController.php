<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceSyncController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user && $user->isActive()
                && ($user->isAdmin() || $user->isChildOfficer()),
            403,
            'You are not allowed to synchronize attendance.'
        );

        $batch = Validator::make($request->all(), [
            'events' => ['required', 'array', 'min:1', 'max:100'],
        ])->validate();

        $results = [];

        foreach ($batch['events'] as $index => $payload) {
            $results[] = $this->processEvent($payload, $index, $request);
        }

        return response()->json(['results' => $results]);
    }

    private function processEvent(mixed $payload, int $index, Request $request): array
    {
        if (!is_array($payload)) {
            return [
                'index' => $index,
                'status' => 'rejected',
                'message' => 'This attendance item must be an object.',
            ];
        }

        $validator = Validator::make($payload, [
            'event_uuid' => ['required', 'uuid'],
            'child_code' => ['required', 'string', 'max:30'],
            'event_id' => ['required', 'integer', 'min:1'],
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'attended_at' => ['required', 'date'],
            'device_id' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return [
                'index' => $index,
                'event_uuid' => $payload['event_uuid'] ?? null,
                'status' => 'rejected',
                'message' => 'Attendance details are invalid.',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $data = $validator->validated();

        $child = Child::where('child_code', trim($data['child_code']))->first();
        $event = Event::find($data['event_id']);

        if (!$child || $child->status !== 'active') {
            return $this->result(
                $index,
                $data['event_uuid'],
                'rejected',
                'Child not found or inactive.'
            );
        }

        if (!$event || !$event->enable_attendance) {
            return $this->result(
                $index,
                $data['event_uuid'],
                'rejected',
                'Event attendance is unavailable.'
            );
        }

        $clientTime = CarbonImmutable::parse($data['attended_at'])->utc();

        // This hash lets the server detect an event ID reused
        // with different attendance details.
        $payloadHash = hash('sha256', json_encode([
            'child_code' => $child->child_code,
            'event_id' => (int) $event->id,
            'attendance_date' => $data['attendance_date'],
            'attended_at' => $clientTime->toISOString(),
            'device_id' => $data['device_id'] ?? null,
        ], JSON_THROW_ON_ERROR));

        $sameEvent = Attendance::where('event_uuid', $data['event_uuid'])->first();

        if ($sameEvent) {
            return $this->sameEventResult(
                $index,
                $data['event_uuid'],
                $sameEvent,
                $payloadHash
            );
        }

        $sameChildAndEvent = Attendance::where('child_id', $child->id)
            ->where('event_id', $event->id)
            ->first();

        if ($sameChildAndEvent) {
            return $this->result(
                $index,
                $data['event_uuid'],
                'already_recorded',
                'Attendance is already recorded for this child and event.',
                $sameChildAndEvent->id
            );
        }

        try {
            $attendance = DB::transaction(function () use (
                $data,
                $child,
                $event,
                $clientTime,
                $payloadHash,
                $request
            ) {
                return Attendance::create([
                    'child_id' => $child->id,
                    'event_id' => $event->id,
                    'marked_by' => $request->user()->id,

                    // The server records when it received the upload.
                    'attendance_time' => now(),

                    // Keep the device's time separately for review.
                    'attendance_date' => $data['attendance_date'],
                    'event_uuid' => $data['event_uuid'],
                    'device_id' => $data['device_id'] ?? null,
                    'client_attended_at' => $clientTime,
                    'sync_payload_hash' => $payloadHash,

                    'method' => 'qr',
                    'status' => 'present',
                ]);
            });

            return $this->result(
                $index,
                $data['event_uuid'],
                'accepted',
                'Attendance synchronized.',
                $attendance->id
            );
        } catch (QueryException $exception) {
            // A database unique rule protects against two devices
            // uploading the same child and event at the same time.
            $sameEvent = Attendance::where('event_uuid', $data['event_uuid'])->first();

            if ($sameEvent) {
                return $this->sameEventResult(
                    $index,
                    $data['event_uuid'],
                    $sameEvent,
                    $payloadHash
                );
            }

            $sameChildAndEvent = Attendance::where('child_id', $child->id)
                ->where('event_id', $event->id)
                ->first();

            if ($sameChildAndEvent) {
                return $this->result(
                    $index,
                    $data['event_uuid'],
                    'already_recorded',
                    'Attendance is already recorded for this child and event.',
                    $sameChildAndEvent->id
                );
            }

            // Don't hide unrelated database errors.
            throw $exception;
        }
    }

    private function sameEventResult(
        int $index,
        string $eventUuid,
        Attendance $attendance,
        string $payloadHash
    ): array {
        if (
            $attendance->sync_payload_hash
            && hash_equals($attendance->sync_payload_hash, $payloadHash)
        ) {
            return $this->result(
                $index,
                $eventUuid,
                'accepted',
                'This event was already synchronized.',
                $attendance->id
            );
        }

        return $this->result(
            $index,
            $eventUuid,
            'conflict',
            'This event ID was already used with different details.',
            $attendance->id
        );
    }

    private function result(
        int $index,
        string $eventUuid,
        string $status,
        string $message,
        ?int $attendanceId = null
    ): array {
        return array_filter([
            'index' => $index,
            'event_uuid' => $eventUuid,
            'status' => $status,
            'message' => $message,
            'attendance_id' => $attendanceId,
        ], static fn ($value) => $value !== null);
    }
}