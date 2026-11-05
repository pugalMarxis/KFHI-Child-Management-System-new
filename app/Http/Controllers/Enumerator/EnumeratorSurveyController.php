<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Enumerator;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Services\DuplicatePreventionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EnumeratorSurveyController extends Controller
{
    public function create(Request $request, Questionnaire $questionnaire): View|RedirectResponse
    {
        $enumeratorId = session('verified_enumerator_id');
        if (!$enumeratorId) {
            return redirect()->route('enumerator.verify')
                ->with('info', 'Please verify your identity before opening a survey form.');
        }

        $enumerator = Enumerator::find($enumeratorId);
        if (!$enumerator || !$enumerator->isActive()) {
            return redirect()->route('enumerator.verify')
                ->withErrors(['enumerator_code' => 'Enumerator profile inactive or not found.']);
        }

        abort_unless(
            $questionnaire->isPublished(),
            403,
            'This questionnaire is not published.'
        );

        $questionnaire->load([
            'event',
            'questions.options',
        ]);

        $startedAt = now()->toIso8601String();

        // Pass child field mapping so the frontend knows how to route scanned QR attributes to inputs
        $fieldMapping = $questionnaire->child_field_mapping ?? [];

        return view('enumerator.survey', compact('questionnaire', 'enumerator', 'startedAt', 'fieldMapping'));
    }

    public function store(
        Request $request,
        Questionnaire $questionnaire,
        DuplicatePreventionService $duplicateService
    ): RedirectResponse {
        $enumeratorId = session('verified_enumerator_id');
        if (!$enumeratorId) {
            return redirect()->route('enumerator.verify')
                ->with('info', 'Please verify your identity before submitting a survey.');
        }

        $enumerator = Enumerator::find($enumeratorId);
        if (!$enumerator || !$enumerator->isActive()) {
            return redirect()->route('enumerator.verify')
                ->withErrors(['enumerator_code' => 'Enumerator profile inactive or not found.']);
        }

        abort_unless(
            $questionnaire->isPublished(),
            403,
            'This questionnaire is not published.'
        );

        // Build dynamic server-side validation rules
        $rules = [
            'child_id' => ['nullable', 'integer', 'exists:children,id'],
            'started_at' => ['nullable', 'string'],
        ];
        $customAttributes = [];

        foreach ($questionnaire->questions as $question) {
            $field = 'question_' . $question->id;
            $fieldRules = [];

            if ($question->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($question->question_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    $minVal = $question->setting('min_value');
                    $maxVal = $question->setting('max_value');
                    if ($minVal !== null && $minVal !== '') {
                        $fieldRules[] = 'min:' . $minVal;
                    }
                    if ($maxVal !== null && $maxVal !== '') {
                        $fieldRules[] = 'max:' . $maxVal;
                    }
                    break;

                case 'text':
                case 'phone':
                    $fieldRules[] = 'string';
                    $maxLen = $question->setting('max_length') ?: 255;
                    $minLen = $question->setting('min_length');
                    $fieldRules[] = 'max:' . $maxLen;
                    if ($minLen) {
                        $fieldRules[] = 'min:' . $minLen;
                    }
                    break;

                case 'textarea':
                    $fieldRules[] = 'string';
                    $maxLen = $question->setting('max_length') ?: 5000;
                    $fieldRules[] = 'max:' . $maxLen;
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'time':
                    $fieldRules[] = 'string';
                    break;

                case 'checkbox':
                    $fieldRules[] = 'array';
                    break;

                case 'radio':
                case 'select':
                case 'poll':
                case 'yes_no':
                case 'rating':
                    $fieldRules[] = 'string';
                    break;
            }

            $rules[$field] = $fieldRules;
            $customAttributes[$field] = $question->question_text;
        }

        $validated = $request->validate($rules, [], $customAttributes);

        // Extract and check primary identifier
        $extracted = $duplicateService->extractIdentifier($questionnaire, $validated);

        if ($extracted['has_identifier'] && $extracted['hash']) {
            if ($duplicateService->isDuplicate($questionnaire, $extracted['hash'])) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'primary_identifier' => 'This record already exists. A submission with this identifier has already been registered.',
                    ])
                    ->with('error', 'This record already exists. A submission with this identifier has already been registered.');
            }
        }

        // Child snapshot if linked
        $childId = $validated['child_id'] ?? null;
        $childSnapshot = null;

        if ($childId) {
            $child = Child::find($childId);
            if ($child) {
                $childSnapshot = [
                    'child_code' => $child->child_code,
                    'first_name' => $child->first_name,
                    'middle_name' => $child->middle_name,
                    'last_name' => $child->last_name,
                    'gender' => $child->gender,
                    'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                    'school_name' => $child->school_name,
                    'grade' => $child->grade,
                    'district' => $child->district,
                    'gn_division' => $child->gn_division,
                    'address' => $child->address,
                    'status' => $child->status,
                ];
            }
        }

        // Store response and answers safely in staging (CHILD RECORD IS NOT MODIFIED DIRECTLY)
        $responseUuid = (string) Str::uuid();
        $startedAt = !empty($validated['started_at']) ? parse_url($validated['started_at'], PHP_URL_HOST) ?? now() : now();

        DB::transaction(function () use (
            $questionnaire,
            $enumerator,
            $childId,
            $childSnapshot,
            $validated,
            $extracted,
            $responseUuid
        ) {
            $response = QuestionnaireResponse::create([
                'questionnaire_id' => $questionnaire->id,
                'child_id' => $childId,
                'submitted_by' => auth()->id(),
                'enumerator_id' => $enumerator->id,
                'enumerator_name' => $enumerator->name,
                'primary_identifier_value' => $extracted['value'] ?? ($childSnapshot['child_code'] ?? null),
                'primary_identifier_hash' => $extracted['hash'] ?? null,
                'started_at' => now(), // Meaningful survey begin timestamp
                'submitted_at' => now(),
                'response_uuid' => $responseUuid,
                'review_status' => 'submitted',
                'merge_status' => 'pending',
                'child_snapshot' => $childSnapshot,
            ]);

            foreach ($questionnaire->questions as $question) {
                $fieldName = 'question_' . $question->id;
                $val = $validated[$fieldName] ?? null;

                if (is_array($val)) {
                    $response->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => null,
                        'answer_json' => $val,
                    ]);
                } else {
                    $response->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => $val !== null ? (string) $val : null,
                        'answer_json' => null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('enumerator.dashboard')
            ->with('success', 'Survey response submitted successfully! The response has been queued for review and will remain pending until approved.');
    }
}
