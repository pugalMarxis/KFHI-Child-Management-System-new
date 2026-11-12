<?php

namespace App\Http\Controllers;

use App\Models\Enumerator;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Services\DuplicatePreventionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicFormController extends Controller
{
    public function show(string $token): View
    {
        $questionnaire = Questionnaire::where('public_token', $token)
            ->with(['event', 'questions.options'])
            ->firstOrFail();

        abort_unless(
            $questionnaire->isPublished(),
            403,
            'This questionnaire is not currently accepting public submissions.'
        );

        abort_unless(
            $questionnaire->allow_anonymous || session()->has('verified_enumerator_id') || auth()->check(),
            403,
            'This form requires survey enumerator access. Please enter as an enumerator.'
        );

        $this->authorizeEnumeratorAssignment($questionnaire);

        $fieldMapping = $questionnaire->child_field_mapping ?? [];
        // $commonPassword = \App\Models\Enumerator::getCommonPassword();

        // return view('public_forms.show', compact('questionnaire', 'fieldMapping', 'commonPassword'));
        return view('public_forms.show', compact('questionnaire', 'fieldMapping'));
    }

    /**
     * Public QR Code lookup for child identification and autofill.
     */
    public function qrLookup(Request $request)
    {
        return app(\App\Http\Controllers\ChildOfficer\QRCodeController::class)->lookup($request);
    }

    /**
     * Inline verification for field enumerator accessing via public form link.
     */
    public function verifyEnumerator(Request $request)
    {
        $validated = $request->validate([
            'enumerator_name' => ['required', 'string', 'max:255'],
            'access_password' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (!\App\Models\Enumerator::verifyCommonPassword($validated['access_password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Common Access Password. Please check with your supervisor.',
            ], 422);
        }

       
            $name = trim($validated['enumerator_name']);
            $enumerator = \App\Models\Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->where(function ($q) use ($name) {
                    $q->where('name', $name)
                    ->orWhere('enumerator_code', strtoupper($name));
                })->first();

        if (!$enumerator) {
            $lastId = \App\Models\Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->max('id') ?? 0;

            $enumerator = \App\Models\Enumerator::create([
                'enumerator_code' => 'ENUM-' . str_pad((string) ($lastId + 1), 3, '0', STR_PAD_LEFT),
                'name' => $name,
                'phone' => $validated['phone'] ?? null,
                'pin_code' => $validated['access_password'],
                'status' => 'active',
                'created_by' => auth()->id() ?? 1,
            ]);
        }

        if (!$enumerator->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Enumerator account inactive. Please ask your supervisor for help.',
            ], 422);
        }

        session([
            'verified_enumerator_id' => $enumerator->id,
            'verified_enumerator_code' => $enumerator->enumerator_code,
            'verified_enumerator_name' => $enumerator->name,
        ]);

        return response()->json([
            'success' => true,
            'enumerator' => [
                'id' => $enumerator->id,
                'code' => $enumerator->enumerator_code,
                'name' => $enumerator->name,
            ],
            'message' => "Welcome, {$enumerator->name}! Enumerator mode unlocked with ID: {$enumerator->enumerator_code}.",
        ]);
    }

    public function store(
        Request $request,
        string $token,
        DuplicatePreventionService $duplicateService
    ): RedirectResponse {
        $questionnaire = Questionnaire::where('public_token', $token)
            ->with(['event', 'questions.options'])
            ->firstOrFail();

        abort_unless(
            $questionnaire->isPublished(),
            403,
            'This questionnaire is not currently accepting public submissions.'
        );

        abort_unless(
            $questionnaire->allow_anonymous
                || session()->has('verified_enumerator_id')
                || auth()->check(),
            403,
            'This form requires authenticated survey enumerator access.'
        );

        $enumerator = $this->authorizeEnumeratorAssignment($questionnaire);

        // Build dynamic server-side validation rules
        $rules = [
            'child_id' => ['nullable', 'integer', 'exists:children,id'],
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

        // Duplicate prevention check
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

        // Only a verified Enumerator may link a response to an assigned child.
        $childId = $validated['child_id'] ?? null;
        $childSnapshot = null;

        if ($childId) {
            abort_unless(
                $enumerator,
                403,
                'Child records can only be linked by a verified Enumerator.'
            );

            $child = \App\Models\Child::query()
                ->whereKey($childId)
                ->where('status', 'active')
                ->first();

            abort_unless(
                $child && $enumerator->allowedChildren()->whereKey($child->getKey())->exists(),
                403,
                'This child is not assigned to your Enumerator account.'
            );

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

        // Determine enumerator attribution
        $enumeratorId = session('verified_enumerator_id');
        $enumeratorName = session('verified_enumerator_name');

        if (!$enumeratorId && $request->filled('enumerator_name')) {
            $enumeratorName = trim($request->input('enumerator_name'));
        }

        if (!$enumeratorName) {
            $enumeratorName = 'Public Respondent';
        }

        // Store response and answers atomically
        $responseUuid = (string) Str::uuid();

        DB::transaction(function () use (
            $questionnaire,
            $validated,
            $extracted,
            $responseUuid,
            $childId,
            $childSnapshot,
            $enumeratorId,
            $enumeratorName
        ) {
            $response = QuestionnaireResponse::create([
                'questionnaire_id' => $questionnaire->id,
                'child_id' => $childId,
                'submitted_by' => auth()->id(),
                'enumerator_id' => $enumeratorId,
                'enumerator_name' => $enumeratorName,
                'primary_identifier_value' => $extracted['value'] ?? ($childSnapshot['child_code'] ?? null),
                'primary_identifier_hash' => $extracted['hash'] ?? null,
                'started_at' => now(),
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
            ->route('forms.success', $token)
            ->with('success', 'Your submission has been received successfully.');
    }

    /**
     * Enforce Enumerator assignment for questionnaires that require it.
     */
    private function authorizeEnumeratorAssignment(Questionnaire $questionnaire): ?Enumerator
    {
        $enumeratorId = session('verified_enumerator_id');

        if ($questionnaire->requires_enumerator) {
            abort_unless(
                $enumeratorId,
                403,
                'This questionnaire requires a verified Enumerator account.'
            );
        }

        if (!$enumeratorId) {
            return null;
        }

        $enumerator = Enumerator::find($enumeratorId);

        abort_unless(
            $enumerator && $enumerator->isActive(),
            403,
            'Enumerator access is inactive or expired.'
        );

        if ($questionnaire->requires_enumerator) {
            abort_unless(
                $enumerator->questionnaires()->whereKey($questionnaire->getKey())->exists(),
                403,
                'This questionnaire is not assigned to your Enumerator account.'
            );
        }

        return $enumerator;
    }

    public function success(string $token): View
    {
        $questionnaire = Questionnaire::where('public_token', $token)
            ->with('event')
            ->firstOrFail();

        return view('public_forms.success', compact('questionnaire'));
    }
}
