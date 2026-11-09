<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Enumerator;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class EnumeratorEventAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Enumerator $enumerator;
    private Event $event;
    private Questionnaire $questionnaire;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Attendance Admin',
            'username' => 'attendance_admin',
            'email' => 'attendance-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $this->event = Event::create([
            'event_code' => 'ENUM-ATT-01',
            'title' => 'Enumerator attendance event',
            'event_date' => now()->toDateString(),
            'status' => 'planned',
            'enable_attendance' => true,
            'created_by' => $this->admin->id,
        ]);
        $this->enumerator = Enumerator::create([
            'enumerator_code' => 'ENUM-ATT-01',
            'name' => 'Field Enumerator',
            'pin_code' => '1234',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
        $this->questionnaire = Questionnaire::create([
            'event_id' => $this->event->id,
            'title' => 'Enumerator attendance form',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $this->enumerator->questionnaires()->attach($this->questionnaire->id);
        $this->child = Child::create([
            'child_code' => 'ENUM-CHILD-01',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
        $this->enumerator->allowedChildren()->attach($this->child->id, [
            'allowed_by' => $this->admin->id,
        ]);
    }

    public function test_enumerator_attendance_is_saved_for_survey_event_and_shown_on_event_page(): void
    {
        $payload = $this->payload();

        $this->withSession(['verified_enumerator_id' => $this->enumerator->id])
            ->postJson(route('enumerator.attendance.sync', $this->event), ['events' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $this->assertDatabaseHas('attendances', [
            'child_id' => $this->child->id,
            'event_id' => $this->event->id,
            'event_uuid' => $payload['event_uuid'],
            'marked_by' => null,
            'marked_by_enumerator_id' => $this->enumerator->id,
            'status' => 'present',
        ]);

        $this->actingAs($this->admin)
            ->get(route('events.attendance.index', $this->event))
            ->assertOk()
            ->assertSee('Test Child')
            ->assertSee('Field Enumerator');
    }

    public function test_retry_and_second_device_do_not_create_duplicate_event_attendance(): void
    {
        $first = $this->payload();
        $this->withSession(['verified_enumerator_id' => $this->enumerator->id])
            ->postJson(route('enumerator.attendance.sync', $this->event), ['events' => [$first]])
            ->assertJsonPath('results.0.status', 'accepted');

        $this->postJson(route('enumerator.attendance.sync', $this->event), ['events' => [$first]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $secondDevice = $this->payload();
        $secondDevice['device_id'] = 'another-phone';
        $this->postJson(route('enumerator.attendance.sync', $this->event), ['events' => [$secondDevice]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'already_recorded');

        $this->assertSame(1, Attendance::count());
    }

    public function test_enumerator_cannot_mark_a_child_not_assigned_to_them(): void
    {
        $this->enumerator->allowedChildren()->detach($this->child->id);

        $this->withSession(['verified_enumerator_id' => $this->enumerator->id])
            ->postJson(route('enumerator.attendance.sync', $this->event), ['events' => [$this->payload()]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'rejected');

        $this->assertSame(0, Attendance::count());
    }

    public function test_admin_can_download_the_event_attendance_excel_file(): void
    {
        Attendance::create([
            'child_id' => $this->child->id,
            'event_id' => $this->event->id,
            'marked_by_enumerator_id' => $this->enumerator->id,
            'attendance_time' => now(),
            'attendance_date' => now('Asia/Colombo')->toDateString(),
            'event_uuid' => (string) Str::uuid(),
            'sync_payload_hash' => str_repeat('a', 64),
            'method' => 'enumerator_qr',
            'status' => 'present',
        ]);

        Excel::fake();
        $fileName = 'attendance_' . $this->event->event_code . '_' . now()->format('Ymd_His') . '.xlsx';

        $this->actingAs($this->admin)
            ->get(route('events.attendance.export', $this->event))
            ->assertOk();

        Excel::assertDownloaded($fileName);
    }

    private function payload(): array
    {
        return [
            'event_uuid' => (string) Str::uuid(),
            'questionnaire_id' => $this->questionnaire->id,
            'child_code' => $this->child->child_code,
            'attendance_date' => now('Asia/Colombo')->toDateString(),
            'attended_at' => now('Asia/Colombo')->toIso8601String(),
            'device_id' => 'enumerator-phone',
        ];
    }
}
