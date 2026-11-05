<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Enumerator;
use App\Models\Event;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuestionnaireBuilderAndEnumeratorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $childOfficer;
    private User $enumeratorUser;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin User',
            'username' => 'admin_test',
            'email'    => 'admin@kfhi.test',
            'password' => Hash::make('secret123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->childOfficer = User::create([
            'name'     => 'Child Officer',
            'username' => 'officer_test',
            'email'    => 'officer@kfhi.test',
            'password' => Hash::make('secret123'),
            'role'     => 'child_officer',
            'status'   => 'active',
        ]);

        $this->enumeratorUser = User::create([
            'name'     => 'Field Enumerator Session',
            'username' => 'enum_session',
            'email'    => 'enumerator@kfhi.test',
            'password' => Hash::make('secret123'),
            'role'     => 'enumerator',
            'status'   => 'active',
        ]);

        $this->event = Event::create([
            'event_code'  => 'EVT-TEST-001',
            'title'       => 'Community Health & Nutrition Camp',
            'description' => 'Annual health screening event',
            'event_date'  => now()->addDays(5)->toDateString(),
            'start_time'  => '09:00:00',
            'end_time'    => '16:00:00',
            'location'    => 'Colombo Center',
            'status'      => 'planned',
            'created_by'  => $this->admin->id,
        ]);
    }

    // =========================================================================
    // 1. ACCESS CONTROL
    // =========================================================================

    public function test_admin_and_child_officer_can_access_form_builder_pages(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Child Growth Assessment',
            'status'     => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Admin sees form builder
        $this->actingAs($this->admin)
            ->get(route('events.questionnaires.show', [$this->event, $questionnaire]))
            ->assertOk()
            ->assertSee('Child Growth Assessment')
            ->assertSee('Add Question')
            ->assertSee('Publish Questionnaire');

        // Child Officer can access responses index
        $this->actingAs($this->childOfficer)
            ->get(route('child_officer.responses.index'))
            ->assertOk()
            ->assertSee('Form Responses');

        // Child Officer can access enumerators index
        $this->actingAs($this->childOfficer)
            ->get(route('child_officer.enumerators.index'))
            ->assertOk()
            ->assertSee('Survey Enumerators');
    }

    public function test_admin_can_view_and_update_event_edit_page(): void
    {
        // View edit page
        $this->actingAs($this->admin)
            ->get(route('events.edit', $this->event))
            ->assertOk()
            ->assertSee('Edit Event')
            ->assertSee($this->event->event_code)
            ->assertSee($this->event->title);

        // Update event details
        $this->actingAs($this->admin)
            ->put(route('events.update', $this->event), [
                'event_code' => 'EVT-TEST-UPDATED',
                'title' => 'Updated Community Health Camp',
                'event_date' => now()->addDays(10)->toDateString(),
                'status' => 'ongoing',
                'location' => 'Kandy Medical Center',
                'enable_attendance' => 1,
                'enable_questionnaires' => 1,
            ])
            ->assertRedirect(route('events.show', $this->event));

        $this->event->refresh();
        $this->assertEquals('EVT-TEST-UPDATED', $this->event->event_code);
        $this->assertEquals('Updated Community Health Camp', $this->event->title);
        $this->assertEquals('ongoing', $this->event->status);
        $this->assertTrue($this->event->enable_attendance);
        $this->assertTrue($this->event->enable_questionnaires);
    }

    // =========================================================================
    // 2. QUESTION CREATION — all 13 supported types
    // =========================================================================

    public function test_can_add_questions_including_email_and_phone_types(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Household Background Survey',
            'status'     => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $types = [
            ['question_text' => 'Full Name',        'question_type' => 'text',     'is_required' => 1],
            ['question_text' => 'Guardian Email',   'question_type' => 'email',    'is_required' => 0],
            ['question_text' => 'Contact Number',   'question_type' => 'phone',    'is_required' => 1],
            ['question_text' => 'Age in Years',     'question_type' => 'number',   'is_required' => 1, 'min_value' => 1, 'max_value' => 18],
            ['question_text' => 'Description',      'question_type' => 'textarea', 'is_required' => 0],
            ['question_text' => 'Event Date',       'question_type' => 'date',     'is_required' => 1],
            ['question_text' => 'Appointment Time', 'question_type' => 'time',     'is_required' => 0],
            ['question_text' => 'Has Disability?',  'question_type' => 'yes_no',   'is_required' => 1],
            ['question_text' => 'Rate the service', 'question_type' => 'rating',   'is_required' => 0],
        ];

        foreach ($types as $payload) {
            $this->actingAs($this->admin)
                ->post(route('events.questionnaires.questions.store', [$this->event, $questionnaire]), $payload)
                ->assertRedirect();
        }

        // Radio with options
        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.questions.store', [$this->event, $questionnaire]), [
                'question_text' => 'Gender',
                'question_type' => 'radio',
                'is_required'   => 1,
                'options'       => ['Male', 'Female', 'Other'],
            ])
            ->assertRedirect();

        // Checkbox with options
        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.questions.store', [$this->event, $questionnaire]), [
                'question_text' => 'Services Received',
                'question_type' => 'checkbox',
                'is_required'   => 0,
                'options'       => ['Nutrition Pack', 'Medical Checkup', 'School Kit'],
            ])
            ->assertRedirect();

        // Dropdown with options
        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.questions.store', [$this->event, $questionnaire]), [
                'question_text' => 'District',
                'question_type' => 'select',
                'is_required'   => 1,
                'options'       => ['Colombo', 'Gampaha', 'Kandy'],
            ])
            ->assertRedirect();

        // Poll with options
        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.questions.store', [$this->event, $questionnaire]), [
                'question_text' => 'Preferred session',
                'question_type' => 'poll',
                'is_required'   => 0,
                'options'       => ['Morning', 'Afternoon'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Guardian Email',
            'question_type'    => 'email',
        ]);
        $this->assertDatabaseHas('questions', [
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Contact Number',
            'question_type'    => 'phone',
        ]);

        $genderQ = Question::where('question_text', 'Gender')
            ->where('questionnaire_id', $questionnaire->id)
            ->first();
        $this->assertNotNull($genderQ);
        $this->assertCount(3, $genderQ->options);
    }

    // =========================================================================
    // 3. DRAG-AND-DROP REORDER
    // =========================================================================

    public function test_can_reorder_questions_via_json_endpoint(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Reordering Test Survey',
            'status'     => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $q1 = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'First Question',
            'question_type'    => 'text',
            'sort_order'       => 0,
        ]);
        $q2 = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Second Question',
            'question_type'    => 'text',
            'sort_order'       => 1,
        ]);

        $this->actingAs($this->admin)
            ->postJson(
                route('events.questionnaires.questions.reorder', [$this->event, $questionnaire]),
                ['question_ids' => [$q2->id, $q1->id]]
            )
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $q2->fresh()->sort_order);
        $this->assertEquals(1, $q1->fresh()->sort_order);
    }

    // =========================================================================
    // 4. PUBLISH & PUBLIC TOKEN GENERATION
    // =========================================================================

    public function test_questionnaire_publishing_generates_unique_public_token(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Public Community Survey',
            'status'     => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Attempting to publish without questions shows error
        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.publish', [$this->event, $questionnaire]))
            ->assertSessionHas('error');

        // Add a question, then publish
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Child Name',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('events.questionnaires.publish', [$this->event, $questionnaire]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $questionnaire->refresh();
        $this->assertTrue($questionnaire->isPublished());
        $this->assertNotEmpty($questionnaire->public_token);
        $this->assertStringContainsString('/forms/' . $questionnaire->public_token, $questionnaire->public_url);
        $this->assertStringContainsString('api.whatsapp.com', $questionnaire->whatsapp_share_url);
    }

    // =========================================================================
    // 5. PUBLIC UNAUTHENTICATED FORM SUBMISSION & FIELD VALIDATION
    // =========================================================================

    public function test_public_form_submission_validates_required_and_email_fields(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'       => $this->event->id,
            'title'          => 'Open Community Feedback',
            'status'         => 'published',
            'public_token'   => 'token-open-feedback-123',
            'allow_anonymous'=> true,
            'created_by'     => $this->admin->id,
        ]);

        $nameQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Full Name',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);

        $emailQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Your Email',
            'question_type'    => 'email',
            'is_required'      => true,
        ]);

        // Anonymous user visits the public form
        $this->get(route('forms.show', $questionnaire->public_token))
            ->assertOk()
            ->assertSee('Open Community Feedback')
            ->assertSee('Full Name')
            ->assertSee('Your Email');

        // Missing required fields → validation error
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $nameQ->id  => '',
            'question_' . $emailQ->id => 'not-an-email',
        ])->assertSessionHasErrors([
            'question_' . $nameQ->id,
            'question_' . $emailQ->id,
        ]);

        // Valid submission → redirects to success page
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $nameQ->id  => 'Jane Doe',
            'question_' . $emailQ->id => 'jane@example.com',
        ])->assertRedirect(route('forms.success', $questionnaire->public_token));

        $this->assertDatabaseHas('questionnaire_responses', [
            'questionnaire_id' => $questionnaire->id,
            'review_status'    => 'submitted',
            'merge_status'     => 'pending',
        ]);

        $this->assertDatabaseHas('questionnaire_answers', [
            'question_id' => $nameQ->id,
            'answer_text' => 'Jane Doe',
        ]);
    }

    // =========================================================================
    // 6. PRIMARY IDENTIFIER DUPLICATE PREVENTION (SINGLE FIELD)
    // =========================================================================

    public function test_primary_identifier_duplicate_prevention_single_field(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'       => $this->event->id,
            'title'          => 'Child Health Registration',
            'status'         => 'published',
            'public_token'   => 'health-reg-token-2026',
            'allow_anonymous'=> true,
            'created_by'     => $this->admin->id,
        ]);

        $idQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'National ID / Child Code',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);
        $nameQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Full Name',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);

        // Configure single primary identifier
        $questionnaire->update([
            'primary_identifier_settings' => [
                'question_ids' => [$idQ->id],
                'scope'        => 'questionnaire',
            ],
        ]);

        // 1st submission → should succeed
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $idQ->id   => 'NIC-778899',
            'question_' . $nameQ->id => 'First Child',
        ])->assertRedirect(route('forms.success', $questionnaire->public_token));

        $this->assertDatabaseCount('questionnaire_responses', 1);

        // 2nd submission with duplicate NIC (trimmed + lowercase) → rejected
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $idQ->id   => '  nic-778899  ',
            'question_' . $nameQ->id => 'Attempted Duplicate Child',
        ])->assertSessionHasErrors();

        // Only 1 response recorded
        $this->assertDatabaseCount('questionnaire_responses', 1);

        // 3rd submission with different NIC → should succeed
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $idQ->id   => 'NIC-990011',
            'question_' . $nameQ->id => 'Different Child',
        ])->assertRedirect(route('forms.success', $questionnaire->public_token));

        $this->assertDatabaseCount('questionnaire_responses', 2);
    }

    // =========================================================================
    // 7. PRIMARY IDENTIFIER DUPLICATE PREVENTION (COMPOSITE FIELDS)
    // =========================================================================

    public function test_primary_identifier_duplicate_prevention_composite_fields(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'       => $this->event->id,
            'title'          => 'Vaccination Registry',
            'status'         => 'published',
            'public_token'   => 'vaccination-token-2026',
            'allow_anonymous'=> true,
            'created_by'     => $this->admin->id,
        ]);

        $nameQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Full Name',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);
        $dobQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Date of Birth',
            'question_type'    => 'date',
            'is_required'      => true,
        ]);

        // Composite primary identifier: Name + DOB
        $questionnaire->update([
            'primary_identifier_settings' => [
                'question_ids' => [$nameQ->id, $dobQ->id],
                'scope'        => 'questionnaire',
            ],
        ]);

        // 1st: Kasun Silva, 2016-04-12 → success
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $nameQ->id => 'Kasun Silva',
            'question_' . $dobQ->id  => '2016-04-12',
        ])->assertRedirect(route('forms.success', $questionnaire->public_token));

        // 2nd: Duplicate (lowercase + spaces) → rejected
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $nameQ->id => '  kasun silva  ',
            'question_' . $dobQ->id  => '2016-04-12',
        ])->assertSessionHasErrors();

        // 3rd: Same name, DIFFERENT DOB → allowed
        $this->post(route('forms.store', $questionnaire->public_token), [
            'question_' . $nameQ->id => 'Kasun Silva',
            'question_' . $dobQ->id  => '2017-09-01',
        ])->assertRedirect(route('forms.success', $questionnaire->public_token));

        $this->assertDatabaseCount('questionnaire_responses', 2);
    }

    // =========================================================================
    // 8. SURVEY ENUMERATOR VERIFICATION LIFECYCLE
    // =========================================================================

    public function test_survey_enumerator_lifecycle_and_pin_verification(): void
    {
        // Admin creates an enumerator via the management controller
        $this->actingAs($this->admin)
            ->post(route('admin.enumerators.store'), [
                'name'     => 'John Field Worker',
                'phone'    => '0712345678',
                'pin_code' => '5432',
                'status'   => 'active',
            ])
            ->assertRedirect(route('admin.enumerators.index'));

        $enumerator = Enumerator::where('name', 'John Field Worker')->first();
        $this->assertNotNull($enumerator);
        $this->assertStringStartsWith('ENUM-', $enumerator->enumerator_code);
        $this->assertTrue($enumerator->verifyPin('5432'));
        $this->assertFalse($enumerator->verifyPin('9999'));

        // Shared enumerator user logs in
        $this->actingAs($this->enumeratorUser);

        // Before verifying individual PIN → dashboard redirects to verify
        $this->get(route('enumerator.dashboard'))
            ->assertRedirect(route('enumerator.verify'));

        // Wrong PIN → error
        $this->post(route('enumerator.verify.post'), [
            'enumerator_code' => $enumerator->enumerator_code,
            'pin_code'        => '0000',
        ])->assertSessionHasErrors(['pin_code']);

        // Correct PIN → dashboard
        $this->post(route('enumerator.verify.post'), [
            'enumerator_code' => $enumerator->enumerator_code,
            'pin_code'        => '5432',
        ])->assertRedirect(route('enumerator.dashboard'));

        $this->assertEquals($enumerator->id, session('verified_enumerator_id'));

        // Dashboard accessible
        $this->get(route('enumerator.dashboard'))
            ->assertOk()
            ->assertSee($enumerator->name)
            ->assertSee($enumerator->enumerator_code);

        // Switch identity clears verification
        $this->get(route('enumerator.switch'))
            ->assertRedirect(route('enumerator.verify'));
        $this->assertNull(session('verified_enumerator_id'));
    }

    public function test_enumerator_auto_registers_with_name_and_common_password(): void
    {
        // Set common password to 'secret2026'
        Enumerator::setCommonPassword('secret2026');
        $this->assertEquals('secret2026', Enumerator::getCommonPassword());

        // A field enumerator enters their name and common password without being pre-registered
        $response = $this->post(route('enumerator.verify.post'), [
            'enumerator_name' => 'Field Worker Saman',
            'pin_code'        => 'secret2026',
            'phone'           => '0771234567',
        ]);

        $response->assertRedirect(route('enumerator.dashboard'));

        // An Enumerator record was automatically created with an auto-assigned code
        $enumerator = Enumerator::where('name', 'Field Worker Saman')->first();
        $this->assertNotNull($enumerator);
        $this->assertStringStartsWith('ENUM-', $enumerator->enumerator_code);
        $this->assertEquals($enumerator->id, session('verified_enumerator_id'));
        $this->assertEquals('Field Worker Saman', session('verified_enumerator_name'));

        // Admin can update common password
        $this->actingAs($this->admin)
            ->post(route('admin.enumerators.common_password'), [
                'common_password' => 'newpass777',
            ])
            ->assertSessionHas('success');

        $this->assertEquals('newpass777', Enumerator::getCommonPassword());
    }

    public function test_public_form_qr_lookup_and_inline_enumerator_verification(): void
    {
        $child = Child::create([
            'child_code' => 'KFHI-QR-TEST-01',
            'first_name' => 'Nimal',
            'last_name'  => 'Bandara',
            'gender'     => 'Male',
            'district'   => 'Badulla',
            'status'     => 'active',
            'created_by' => $this->admin->id,
        ]);

        // Public QR lookup using child code in offline format
        $offlinePayload = json_encode(['kfhi' => 'offline_v1', 'code' => 'KFHI-QR-TEST-01', 'name' => 'Nimal Bandara']);
        $this->postJson(route('forms.qr.lookup'), ['token' => $offlinePayload])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'child' => [
                    'child_code' => 'KFHI-QR-TEST-01',
                    'full_name'  => 'Nimal Bandara',
                    'district'   => 'Badulla',
                ],
            ]);

        // Inline enumerator verification from public form
        Enumerator::setCommonPassword('1234');
        $this->postJson(route('forms.enumerator.verify'), [
            'enumerator_name' => 'Niroshan Perera',
            'access_password' => '1234',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'enumerator' => [
                'name' => 'Niroshan Perera',
            ],
        ]);

        $this->assertEquals('Niroshan Perera', session('verified_enumerator_name'));
    }

    // =========================================================================
    // 9. ENUMERATOR STAGING — child record MUST NOT be modified
    // =========================================================================

    public function test_enumerator_survey_stages_response_without_overwriting_child(): void
    {
        $child = Child::create([
            'child_code' => 'KFHI-COL-0099',
            'first_name' => 'OriginalFirstName',
            'district'   => 'Colombo',
            'status'     => 'active',
            'created_by' => $this->admin->id,
        ]);

        // Create enumerator via store (hashes PIN correctly)
        $this->actingAs($this->admin)
            ->post(route('admin.enumerators.store'), [
                'name'     => 'Surveyor Anne',
                'phone'    => '0779999999',
                'pin_code' => '1234',
                'status'   => 'active',
            ]);
        $enumerator = Enumerator::where('name', 'Surveyor Anne')->first();

        $questionnaire = Questionnaire::create([
            'event_id'          => $this->event->id,
            'title'             => 'Child Health Survey 2026',
            'status'            => 'published',
            'public_token'      => 'health-survey-token-99',
            'requires_enumerator'=> true,
            'created_by'        => $this->admin->id,
        ]);

        $nameQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Child First Name',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);
        $districtQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'District',
            'question_type'    => 'text',
            'is_required'      => true,
        ]);

        $questionnaire->update([
            'child_field_mapping' => [
                ['child_field' => 'first_name', 'question_id' => $nameQ->id, 'autofill' => true, 'readonly_for_enumerator' => false],
                ['child_field' => 'district',   'question_id' => $districtQ->id, 'autofill' => true, 'readonly_for_enumerator' => false],
            ],
        ]);

        // Submit as verified enumerator with UPDATED values
        $this->actingAs($this->enumeratorUser)
            ->withSession(['verified_enumerator_id' => $enumerator->id])
            ->post(route('enumerator.survey.store', $questionnaire), [
                'child_id'                           => $child->id,
                'question_' . $nameQ->id             => 'UpdatedFirstName',
                'question_' . $districtQ->id         => 'Gampaha',
            ])
            ->assertRedirect(route('enumerator.dashboard'))
            ->assertSessionHas('success');

        // CRITICAL: child record MUST NOT be overwritten
        $child->refresh();
        $this->assertEquals('OriginalFirstName', $child->first_name);
        $this->assertEquals('Colombo', $child->district);

        // Response is safely staged
        $response = QuestionnaireResponse::where('questionnaire_id', $questionnaire->id)->first();
        $this->assertNotNull($response);
        $this->assertEquals($child->id, $response->child_id);
        $this->assertEquals($enumerator->id, $response->enumerator_id);
        $this->assertEquals('submitted', $response->review_status);
        $this->assertEquals('pending', $response->merge_status);
        $this->assertIsArray($response->child_snapshot);
        $this->assertEquals('OriginalFirstName', $response->child_snapshot['first_name']);
    }

    // =========================================================================
    // 10. ADMIN REVIEW, STATUS UPDATE & EXPLICIT MERGE WITH AUDIT LOG
    // =========================================================================

    public function test_admin_response_review_and_explicit_transactional_merge(): void
    {
        $child = Child::create([
            'child_code' => 'KFHI-COL-0100',
            'first_name' => 'OldName',
            'district'   => 'OldDistrict',
            'status'     => 'active',
            'created_by' => $this->admin->id,
        ]);

        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Growth Check 2026',
            'status'     => 'published',
            'created_by' => $this->admin->id,
        ]);

        $nameQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'First Name',
            'question_type'    => 'text',
        ]);
        $districtQ = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'District',
            'question_type'    => 'text',
        ]);

        $questionnaire->update([
            'child_field_mapping' => [
                ['child_field' => 'first_name', 'question_id' => $nameQ->id],
                ['child_field' => 'district',   'question_id' => $districtQ->id],
            ],
        ]);

        // Manually create a staged response (as if submitted by enumerator)
        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id'         => $child->id,
            'review_status'    => 'submitted',
            'merge_status'     => 'pending',
            'submitted_at'     => now(),
            'response_uuid'    => (string) Str::uuid(),
            'child_snapshot'   => $child->only(['first_name', 'district']),
        ]);

        $response->answers()->create(['question_id' => $nameQ->id,     'answer_text' => 'ApprovedNewName']);
        $response->answers()->create(['question_id' => $districtQ->id, 'answer_text' => 'ApprovedNewDistrict']);

        // Reviewer opens review page
        $this->actingAs($this->admin)
            ->get(route('admin.responses.show', $response))
            ->assertOk()
            ->assertSee('Submission #' . $response->id . ' Review')
            ->assertSee('ApprovedNewName')
            ->assertSee('OldName');

        // Reviewer sets status to under_review
        $this->actingAs($this->admin)
            ->post(route('admin.responses.status', $response), [
                'review_status' => 'under_review',
                'review_notes'  => 'Checking birth certificate copy',
            ])
            ->assertRedirect(route('admin.responses.show', $response));

        $this->assertEquals('under_review', $response->fresh()->review_status);

        // Explicit merge of selected fields
        $this->actingAs($this->admin)
            ->post(route('admin.responses.merge', $response), [
                'approved_fields' => ['first_name', 'district'],
                'notes'           => 'Merged after document verification',
            ])
            ->assertRedirect(route('admin.responses.show', $response))
            ->assertSessionHas('success');

        // Child record is now updated
        $child->refresh();
        $this->assertEquals('ApprovedNewName', $child->first_name);
        $this->assertEquals('ApprovedNewDistrict', $child->district);

        // Response is marked merged but NOT deleted
        $response->refresh();
        $this->assertEquals('approved',       $response->review_status);
        $this->assertEquals('merged',         $response->merge_status);
        $this->assertEquals($this->admin->id, $response->merged_by);
        $this->assertNotNull($response->merged_at);
        $this->assertDatabaseHas('questionnaire_responses', ['id' => $response->id]);

        // Audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'merge_child_survey',
            'module'  => 'questionnaire_responses',
            'record_id' => $response->id,
        ]);
    }

    // =========================================================================
    // 11. EXCEL EXPORT — no duplicate records created
    // =========================================================================

    public function test_excel_export_does_not_duplicate_records(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Exportable Survey',
            'status'     => 'published',
            'created_by' => $this->admin->id,
        ]);

        $q = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text'    => 'Notes',
            'question_type'    => 'text',
        ]);

        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'review_status'    => 'approved',
            'merge_status'     => 'pending',
            'submitted_at'     => now(),
            'response_uuid'    => (string) Str::uuid(),
        ]);

        $response->answers()->create([
            'question_id' => $q->id,
            // formula injection attempt — must be sanitized by the export
            'answer_text' => "=cmd|'/C calc'!A0",
        ]);

        // Perform export request — must return binary file response
        $downloadResponse = $this->actingAs($this->admin)
            ->get(route('events.questionnaires.export.excel', [$this->event, $questionnaire]));

        $downloadResponse->assertOk();
        $this->assertTrue(
            str_contains(
                (string) $downloadResponse->headers->get('content-disposition', ''),
                'attachment; filename=questionnaire_exportable_survey_responses_'
            )
        );

        // Records must not have been duplicated during export
        $this->assertDatabaseCount('questionnaire_responses', 1);
        $this->assertDatabaseCount('questionnaire_answers', 1);
    }

    public function test_filtered_response_exports_support_child_only_mode(): void
    {
        $questionnaire = Questionnaire::create([
            'event_id'   => $this->event->id,
            'title'      => 'Camp Registration Follow Up',
            'status'     => 'published',
            'created_by' => $this->admin->id,
        ]);

        $child = Child::create([
            'event_id'   => $this->event->id,
            'child_code' => 'CHD-1001',
            'first_name' => 'Amina',
            'last_name'  => 'Kassim',
            'gender'     => 'female',
            'district'   => 'Kampala',
            'address'    => 'Plot 12, Moyo',
            'status'     => 'active',
            'created_by' => $this->admin->id,
        ]);

        $enumerator = Enumerator::create([
            'name' => 'Hassan Musa',
            'enumerator_code' => 'ENUM-01',
            'phone' => '0771234567',
            'common_password' => 'secret123',
            'status' => 'active',
        ]);

        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => $child->id,
            'enumerator_id' => $enumerator->id,
            'enumerator_name' => 'Hassan Musa',
            'primary_identifier_value' => 'Amina Kassim',
            'review_status' => 'approved',
            'merge_status' => 'merged',
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $downloadResponse = $this->actingAs($this->admin)
            ->get(route('admin.responses.export', [
                'review_status' => 'approved',
                'merge_status' => 'merged',
                'export_type' => 'child',
            ]));

        $downloadResponse->assertOk();
        $this->assertTrue(
            str_contains(
                (string) $downloadResponse->headers->get('content-disposition', ''),
                'attachment; filename=filtered_child_data_'
            )
        );

        $this->assertDatabaseHas('questionnaire_responses', ['id' => $response->id]);
    }
}
