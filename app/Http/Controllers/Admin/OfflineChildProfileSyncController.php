<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Child;
use App\Models\ChildProfileSyncEvent;
use App\Services\QRCodeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfflineChildProfileSyncController extends Controller
{
    public function store(Request $request, QRCodeService $qrCodeService): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && $user->isActive() && ($user->isAdmin() || $user->isChildOfficer()),
            403,
            'You are not allowed to synchronize child records.'
        );

        $batch = Validator::make($request->all(), [
            'operations' => ['required', 'array', 'min:1', 'max:50'],
        ])->validate();

        $results = [];
        foreach ($batch['operations'] as $index => $operation) {
            $results[] = $this->processOperation($operation, $index, $request, $qrCodeService);
        }

        return response()->json(['results' => $results])
            ->header('Cache-Control', 'private, no-store');
    }

    private function processOperation(
        mixed $operation,
        int $index,
        Request $request,
        QRCodeService $qrCodeService
    ): array {
        if (!is_array($operation)) {
            return $this->result($index, null, 'rejected', 'This child change must be an object.');
        }

        $validator = Validator::make($operation, [
            'operation_uuid' => ['required', 'uuid'],
            'action' => ['required', 'in:create,update'],
            'child_id' => ['required_if:action,update', 'integer', 'min:1'],
            'base_profile_version' => ['required_if:action,update', 'integer', 'min:1'],
            'child_code' => ['required', 'string', 'max:30'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date_format:Y-m-d'],
            'gender' => ['required', 'in:male,female,other,Male,Female,Other'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:30'],
            'district' => ['nullable', 'string', 'max:100'],
            'gn_division' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if ($validator->fails()) {
            return $this->result(
                $index,
                $operation['operation_uuid'] ?? null,
                'rejected',
                'Child details are invalid.',
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $data = $validator->validated();
        $operationUuid = $data['operation_uuid'];
        $payloadHash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
        $previous = ChildProfileSyncEvent::where('operation_uuid', $operationUuid)->first();
        if ($previous) {
            return $this->replayOrConflict($index, $previous, $payloadHash);
        }

        try {
            return DB::transaction(function () use (
                $index,
                $data,
                $operationUuid,
                $payloadHash,
                $request,
                $qrCodeService
            ) {
                $child = null;
                if ($data['action'] === 'create') {
                    if (Child::withTrashed()->where('child_code', $data['child_code'])->exists()) {
                        return $this->storeOutcome(
                            $index,
                            $operationUuid,
                            $payloadHash,
                            $request,
                            'rejected',
                            'This child code is already in use. Check the code and try again.'
                        );
                    }

                    $child = Child::create($this->profileFields($data) + [
                        'child_code' => $data['child_code'],
                        'status' => $data['status'] ?? 'active',
                        'created_by' => $request->user()->id,
                    ]);
                    $qrCodeService->generateForChild($child);
                    $action = 'offline_create';
                } else {
                    $child = Child::query()->lockForUpdate()->find($data['child_id']);
                    if (!$child) {
                        return $this->storeOutcome(
                            $index,
                            $operationUuid,
                            $payloadHash,
                            $request,
                            'rejected',
                            'This child record is no longer available.'
                        );
                    }

                    if ((int) $child->profile_version !== (int) $data['base_profile_version']) {
                        return $this->storeOutcome(
                            $index,
                            $operationUuid,
                            $payloadHash,
                            $request,
                            'conflict',
                            'This child changed on the server. Update the saved copy and review your changes before trying again.',
                            $child
                        );
                    }

                    if (!hash_equals($child->child_code, $data['child_code'])) {
                        return $this->storeOutcome(
                            $index,
                            $operationUuid,
                            $payloadHash,
                            $request,
                            'conflict',
                            'The permanent child code cannot be changed.',
                            $child
                        );
                    }

                    $child->fill($this->profileFields($data));
                    if (array_key_exists('status', $data)) {
                        $child->status = $data['status'];
                    }
                    $child->profile_version = ((int) $child->profile_version) + 1;
                    $child->save();
                    $action = 'offline_update';
                }

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => $action,
                    'module' => 'child',
                    'record_id' => $child->id,
                    'description' => 'Child profile synchronized from an offline device.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->storeOutcome(
                    $index,
                    $operationUuid,
                    $payloadHash,
                    $request,
                    'accepted',
                    'Child profile synchronized.',
                    $child->fresh()
                );
            });
        } catch (QueryException $exception) {
            $previous = ChildProfileSyncEvent::where('operation_uuid', $operationUuid)->first();
            if ($previous) {
                return $this->replayOrConflict($index, $previous, $payloadHash);
            }

            if ($data['action'] === 'create'
                && Child::withTrashed()->where('child_code', $data['child_code'])->exists()) {
                return $this->storeOutcome(
                    $index,
                    $operationUuid,
                    $payloadHash,
                    $request,
                    'rejected',
                    'This child code is already in use. Check the code and try again.'
                );
            }

            throw $exception;
        }
    }

    private function storeOutcome(
        int $index,
        string $operationUuid,
        string $payloadHash,
        Request $request,
        string $status,
        string $message,
        ?Child $child = null
    ): array {
        $response = $this->result(
            $index,
            $operationUuid,
            $status,
            $message,
            $child ? ['child_id' => $child->id] : []
        );

        ChildProfileSyncEvent::create([
            'operation_uuid' => $operationUuid,
            'child_id' => $child?->id,
            'user_id' => $request->user()->id,
            'payload_hash' => $payloadHash,
            'status' => $status,
            'response_payload' => $response,
        ]);

        return $child
            ? array_merge($response, ['child' => $this->childData($child)])
            : $response;
    }

    private function replayOrConflict(int $index, ChildProfileSyncEvent $event, string $payloadHash): array
    {
        if (hash_equals($event->payload_hash, $payloadHash) && is_array($event->response_payload)) {
            $response = array_merge($event->response_payload, ['index' => $index]);
            if (!empty($response['child_id'])) {
                $child = Child::find($response['child_id']);
                if ($child) {
                    $response['child'] = $this->childData($child);
                }
            }

            return $response;
        }

        return $this->result(
            $index,
            $event->operation_uuid,
            'conflict',
            'This operation ID was already used for different child details.'
        );
    }

    private function profileFields(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'school_name' => $data['school_name'] ?? null,
            'grade' => $data['grade'] ?? null,
            'district' => $data['district'] ?? null,
            'gn_division' => $data['gn_division'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function childData(Child $child): array
    {
        return [
            'id' => $child->id,
            'child_code' => $child->child_code,
            'first_name' => $child->first_name,
            'middle_name' => $child->middle_name,
            'last_name' => $child->last_name,
            'full_name' => trim(implode(' ', array_filter([
                $child->first_name,
                $child->middle_name,
                $child->last_name,
            ]))),
            'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
            'gender' => $child->gender,
            'school_name' => $child->school_name,
            'grade' => $child->grade,
            'address' => $child->address,
            'district' => $child->district,
            'gn_division' => $child->gn_division,
            'status' => $child->status,
            'profile_version' => (int) $child->profile_version,
            'updated_at' => $child->updated_at?->toIso8601String(),
        ];
    }

    private function result(
        int $index,
        ?string $operationUuid,
        string $status,
        string $message,
        array $extra = []
    ): array {
        return array_merge(array_filter([
            'index' => $index,
            'operation_uuid' => $operationUuid,
            'status' => $status,
            'message' => $message,
        ], static fn ($value) => $value !== null), $extra);
    }
}
