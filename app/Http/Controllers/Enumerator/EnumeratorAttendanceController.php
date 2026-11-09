<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Enumerator;
use App\Models\Event;
use App\Models\Questionnaire;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EnumeratorAttendanceController extends Controller
{
    public function sync(Request $request, Event $event): JsonResponse
    {
        $enumerator = Enumerator::query()->find(session('verified_enumerator_id'));
        abort_unless($enumerator && $enumerator->isActive(), 401, 'Verify your enumerator access before syncing attendance.');

        abort_unless($event->enable_attendance, 422, 'Attendance is not enabled for this event.');

        $batch = Validator::make($request->all(), [
            'events' => ['required', 'array', 'min:1', 'max:100'],
        ])->validate();

        $results = [];
        foreach ($batch['events'] as $index => $payload) {
            $results[] = $this->storeOne($payload, $index, $request, $enumerator, $event->id);
        }

        return response()->json(['results' => $results])
            ->header('Cache-Control', 'private, no-store');
    }

    private function storeOne(
        mixed $payload,
        int $index,
        Request $request,
        Enumerator $enumerator,
        int $eventId
    ): array {
        if (!is_array($payload)) {
            return $this->result($index, null, 'rejected', 'Attendance item is invalid.');
        }

        $validator = Validator::make($payload, [
            'event_uuid' => ['required', 'uuid'],
            'questionnaire_id' => ['required', 'integer', 'min:1'],
            'child_code' => ['required', 'string', 'max:30'],
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'attended_at' => ['required', 'date'],
            'device_id' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->result($index, $payload['event_uuid'] ?? null, 'rejected', 'Attendance details are invalid.');
        }

        $data = $validator->validated();
        $questionnaire = Questionnaire::query()
            ->where('event_id', $eventId)
            ->find($data['questionnaire_id']);
        if (!$questionnaire
            || !$questionnaire->isPublished()
            || !$questionnaire->enumerators()->whereKey($enumerator->id)->exists()) {
            return $this->result($index, $data['event_uuid'], 'rejected', 'This survey is not assigned to your enumerator account.');
        }

        $child = Child::query()
            ->where('child_code', trim($data['child_code']))
            ->where('status', 'active')
            ->first();

        if (!$child || !$enumerator->allowedChildren()->where('children.id', $child->id)->exists()) {
            return $this->result($index, $data['event_uuid'], 'rejected', 'This child is not available to your enumerator account.');
        }

        $attendedAt = CarbonImmutable::parse($data['attended_at'])->utc();
        $payloadHash = hash('sha256', json_encode([
            'questionnaire_id' => $questionnaire->id,
            'event_id' => $eventId,
            'child_code' => $child->child_code,
            'attendance_date' => $data['attendance_date'],
            'attended_at' => $attendedAt->toISOString(),
            'device_id' => $data['device_id'] ?? null,
        ], JSON_THROW_ON_ERROR));

        $existingEvent = Attendance::query()->where('event_uuid', $data['event_uuid'])->first();
        if ($existingEvent) {
            return $this->sameEventResult($index, $data['event_uuid'], $existingEvent, $payloadHash);
        }

        $existingAttendance = Attendance::query()
            ->where('event_id', $eventId)
            ->where('child_id', $child->id)
            ->first();
        if ($existingAttendance) {
            return $this->result($index, $data['event_uuid'], 'already_recorded', 'Attendance is already recorded for this child and event.', $existingAttendance->id);
        }

        try {
            $attendance = DB::transaction(fn () => Attendance::create([
                'child_id' => $child->id,
                'event_id' => $eventId,
                'marked_by' => null,
                'marked_by_enumerator_id' => $enumerator->id,
                'attendance_time' => now(),
                'attendance_date' => $data['attendance_date'],
                'event_uuid' => $data['event_uuid'],
                'device_id' => $data['device_id'] ?? null,
                'client_attended_at' => $attendedAt,
                'sync_payload_hash' => $payloadHash,
                'method' => 'enumerator_qr',
                'status' => 'present',
            ]));

            return $this->result($index, $data['event_uuid'], 'accepted', 'Attendance recorded.', $attendance->id);
        } catch (QueryException $exception) {
            $existingEvent = Attendance::query()->where('event_uuid', $data['event_uuid'])->first();
            if ($existingEvent) {
                return $this->sameEventResult($index, $data['event_uuid'], $existingEvent, $payloadHash);
            }

            $existingAttendance = Attendance::query()
                ->where('event_id', $eventId)
                ->where('child_id', $child->id)
                ->first();
            if ($existingAttendance) {
                return $this->result($index, $data['event_uuid'], 'already_recorded', 'Attendance is already recorded for this child and event.', $existingAttendance->id);
            }

            throw $exception;
        }
    }

    private function sameEventResult(int $index, string $eventUuid, Attendance $attendance, string $payloadHash): array
    {
        if ($attendance->sync_payload_hash && hash_equals($attendance->sync_payload_hash, $payloadHash)) {
            return $this->result($index, $eventUuid, 'accepted', 'Attendance was already synchronized.', $attendance->id);
        }

        return $this->result($index, $eventUuid, 'conflict', 'This attendance event ID was already used with different data.', $attendance->id);
    }

    private function result(int $index, ?string $eventUuid, string $status, string $message, ?int $attendanceId = null): array
    {
        return array_filter([
            'index' => $index,
            'event_uuid' => $eventUuid,
            'status' => $status,
            'message' => $message,
            'attendance_id' => $attendanceId,
        ], static fn ($value) => $value !== null);
    }
}
