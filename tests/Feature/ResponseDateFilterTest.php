<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Enumerator;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ResponseDateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_table_filters_by_submission_date_and_selected_status(): void
    {
        $admin = User::create([
            'name' => 'Response Filter Admin',
            'username' => 'response_filter_admin',
            'email' => 'response-filter-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $event = Event::create([
            'event_code' => 'RESP-FILTER-01',
            'title' => 'Response filter event',
            'event_date' => '2026-11-01',
            'status' => 'planned',
            'created_by' => $admin->id,
        ]);
        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'Response filter questionnaire',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $enumerator = Enumerator::create([
            'enumerator_code' => 'RESP-ENUM-01',
            'name' => 'Selectable Enumerator',
            'pin_code' => '1234',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $outsideRange = $this->makeResponse($questionnaire, '2026-11-01 08:00:00', 'approved');
        $insideRangeWrongStatus = $this->makeResponse($questionnaire, '2026-11-02 08:00:00', 'submitted');
        $matching = $this->makeResponse($questionnaire, '2026-11-03 08:00:00', 'approved');

        $this->actingAs($admin)
            ->get(route('admin.responses.index', [
                'questionnaire_id' => $questionnaire->id,
                'start_date' => '2026-11-02',
                'end_date' => '2026-11-04',
                'review_status' => 'approved',
            ]))
            ->assertOk()
            ->assertSee('Submitted Questionnaire Responses (1)')
            ->assertSee('#' . $matching->id)
            ->assertSee('name="start_date"', false)
            ->assertSee('name="end_date"', false)
            ->assertSee('value="2026-11-02"', false)
            ->assertSee('value="2026-11-04"', false)
            ->assertSee('selectAllVisibleResponses', false)
            ->assertSee('response-row-select', false)
            ->assertSee('selectAllVisibleEnumerators', false)
            ->assertSee('enumerator-row-select', false);

        Excel::fake();
        $this->actingAs($admin)
            ->get(route('admin.responses.export', [
                'export_type' => 'full',
                'response_ids' => [$matching->id],
            ]))
            ->assertOk();

        Excel::matchByRegex();
        Excel::assertDownloaded('/^filtered_response_data_.*\\.xlsx$/', fn ($export) =>
            $export->collection()->modelKeys() === [$matching->id]
        );

        Excel::fake();
        $this->actingAs($admin)
            ->get(route('admin.responses.export-enumerators', [
                'enumerator_ids' => [$enumerator->id],
            ]))
            ->assertOk();

        Excel::matchByRegex();
        Excel::assertDownloaded('/^selected_enumerator_activity_.*\\.xlsx$/', fn ($export) =>
            $export->collection()->modelKeys() === [$enumerator->id]
        );
    }

    public function test_bulk_response_delete_removes_selected_unmerged_responses(): void
    {
        [$admin, $questionnaire] = $this->makeAdminAndQuestionnaire();
        $first = $this->makeResponse($questionnaire, '2026-11-03 08:00:00', 'submitted');
        $second = $this->makeResponse($questionnaire, '2026-11-04 08:00:00', 'approved');

        $this->actingAs($admin)
            ->post(route('admin.responses.bulk-delete'), ['response_ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('questionnaire_responses', ['id' => $first->id]);
        $this->assertDatabaseMissing('questionnaire_responses', ['id' => $second->id]);
    }

    public function test_bulk_response_delete_keeps_entire_selection_if_one_is_merged(): void
    {
        [$admin, $questionnaire] = $this->makeAdminAndQuestionnaire();
        $pending = $this->makeResponse($questionnaire, '2026-11-03 08:00:00', 'submitted');
        $merged = $this->makeResponse($questionnaire, '2026-11-04 08:00:00', 'approved');
        $merged->update(['merge_status' => 'merged']);

        $this->actingAs($admin)
            ->post(route('admin.responses.bulk-delete'), ['response_ids' => [$pending->id, $merged->id]])
            ->assertRedirect()
            ->assertSessionHasErrors('bulk_delete');

        $this->assertDatabaseHas('questionnaire_responses', ['id' => $pending->id]);
        $this->assertDatabaseHas('questionnaire_responses', ['id' => $merged->id]);
    }

    public function test_bulk_enumerator_delete_keeps_submitted_responses(): void
    {
        [$admin, $questionnaire] = $this->makeAdminAndQuestionnaire();
        $enumerator = Enumerator::create([
            'enumerator_code' => 'BULK-ENUM-01',
            'name' => 'Bulk Delete Enumerator',
            'pin_code' => '1234',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $response = $this->makeResponse($questionnaire, '2026-11-05 08:00:00', 'submitted');
        $response->update(['enumerator_id' => $enumerator->id]);

        $this->actingAs($admin)
            ->post(route('admin.enumerators.bulk-delete'), ['enumerator_ids' => [$enumerator->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('enumerators', ['id' => $enumerator->id]);
        $this->assertDatabaseHas('questionnaire_responses', ['id' => $response->id]);
    }

    private function makeResponse(Questionnaire $questionnaire, string $submittedAt, string $reviewStatus): QuestionnaireResponse
    {
        return QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'submitted_at' => $submittedAt,
            'response_uuid' => (string) Str::uuid(),
            'review_status' => $reviewStatus,
            'merge_status' => 'pending',
            'enumerator_name' => 'Test Enumerator',
        ]);
    }

    private function makeAdminAndQuestionnaire(): array
    {
        $admin = User::create([
            'name' => 'Bulk Delete Admin',
            'username' => 'bulk_delete_admin_' . Str::random(6),
            'email' => 'bulk-delete-' . Str::random(6) . '@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $event = Event::create([
            'event_code' => 'BULK-' . strtoupper(Str::random(8)),
            'title' => 'Bulk delete event',
            'event_date' => '2026-11-01',
            'status' => 'planned',
            'created_by' => $admin->id,
        ]);
        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'Bulk delete questionnaire',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        return [$admin, $questionnaire];
    }
}
