<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineAttendanceSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Event $event;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Offline Test Admin',
            'username' => 'offline_admin',
            'email' => 'offline-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->event = Event::create([
            'event_code' => 'OFFLINE-001',
            'title' => 'Offline Attendance Test',
            'event_date' => '2026-11-06',
            'status' => 'planned',
            'enable_attendance' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->child = Child::create([
            'child_code' => 'CHD-OFF-001',
            'first_name' => 'Test Child',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_authorized_user_can_sync_an_offline_attendance_event(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin)
            ->postJson(route('attendance.sync'), ['events' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $this->assertDatabaseHas('attendances', [
            'child_id' => $this->child->id,
            'event_id' => $this->event->id,
            'event_uuid' => $payload['event_uuid'],
            'marked_by' => $this->admin->id,
            'device_id' => $payload['device_id'],
        ]);

        $attendance = Attendance::where('event_uuid', $payload['event_uuid'])->firstOrFail();
        $this->assertSame(
            $payload['attendance_date'],
            $attendance->attendance_date->toDateString()
        );
    }

    public function test_retrying_the_same_event_uuid_does_not_add_another_record(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin)
            ->postJson(route('attendance.sync'), ['events' => [$payload]])
            ->assertJsonPath('results.0.status', 'accepted');

        $this->postJson(route('attendance.sync'), ['events' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $this->assertSame(1, Attendance::count());
    }

    public function test_another_device_duplicate_is_reported_as_already_recorded(): void
    {
        $first = $this->payload();
        $second = $this->payload();

        $this->actingAs($this->admin)
            ->postJson(route('attendance.sync'), ['events' => [$first]])
            ->assertJsonPath('results.0.status', 'accepted');

        $this->postJson(route('attendance.sync'), ['events' => [$second]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'already_recorded');

        $this->assertSame(1, Attendance::count());
    }

    public function test_reusing_an_event_uuid_with_changed_data_is_a_conflict(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin)
            ->postJson(route('attendance.sync'), ['events' => [$payload]])
            ->assertJsonPath('results.0.status', 'accepted');

        $payload['device_id'] = 'different-device';

        $this->postJson(route('attendance.sync'), ['events' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'conflict');

        $this->assertSame(1, Attendance::count());
    }

    public function test_rejected_item_does_not_block_valid_items_in_same_batch(): void
    {
        $invalid = $this->payload();
        $invalid['child_code'] = 'UNKNOWN-CHILD';
        $valid = $this->payload();

        $this->actingAs($this->admin)
            ->postJson(route('attendance.sync'), [
                'events' => [$invalid, $valid],
            ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'rejected')
            ->assertJsonPath('results.1.status', 'accepted');

        $this->assertSame(1, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'event_uuid' => $valid['event_uuid'],
            'child_id' => $this->child->id,
        ]);
    }

    public function test_enumerator_user_cannot_use_the_staff_attendance_sync_route(): void
    {
        $enumerator = User::create([
            'name' => 'Enumerator',
            'username' => 'offline_enumerator',
            'email' => 'offline-enumerator@example.test',
            'password' => 'password',
            'role' => 'enumerator',
            'status' => 'active',
        ]);

        $this->actingAs($enumerator)
            ->postJson(route('attendance.sync'), ['events' => [$this->payload()]])
            ->assertForbidden();

        $this->assertSame(0, Attendance::count());
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'event_uuid' => (string) Str::uuid(),
            'child_code' => $this->child->child_code,
            'event_id' => $this->event->id,
            'attendance_date' => '2026-11-06',
            'attended_at' => '2026-11-06T10:30:00+05:30',
            'device_id' => 'test-device',
        ];
    }
}
