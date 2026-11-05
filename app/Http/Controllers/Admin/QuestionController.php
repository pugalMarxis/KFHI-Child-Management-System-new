<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public const VALID_QUESTION_TYPES = [
        'text',
        'textarea',
        'radio',
        'select',
        'checkbox',
        'poll',
        'number',
        'date',
        'time',
        'yes_no',
        'rating',
        'email',
        'phone',
    ];

    public function create(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        $this->verifyQuestionnaire($event, $questionnaire);

        return view(
            'admin.events.questionnaires.questions.create',
            compact('event', 'questionnaire')
        );
    }

    public function store(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);

        $validated = $request->validate([
            'question_text' => [
                'required',
                'string',
                'max:1000',
            ],
            'question_type' => [
                'required',
                'in:' . implode(',', self::VALID_QUESTION_TYPES),
            ],
            'is_required' => [
                'nullable',
                'boolean',
            ],
            'settings' => [
                'nullable',
                'array',
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'options.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $nextOrder = $questionnaire->questions()->max('sort_order');

        $settings = $this->buildQuestionSettings($request);

        $question = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->boolean('is_required'),
            'sort_order' => ($nextOrder ?? -1) + 1,
            'settings' => $settings,
        ]);

        $this->saveOptions(
            $question,
            $validated['question_type'],
            $validated['options'] ?? []
        );

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question added successfully.');
    }

    public function edit(
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): View {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $question->load('options');

        return view(
            'admin.events.questionnaires.questions.edit',
            compact('event', 'questionnaire', 'question')
        );
    }

    public function update(
        Request $request,
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $validated = $request->validate([
            'question_text' => [
                'required',
                'string',
                'max:1000',
            ],
            'question_type' => [
                'required',
                'in:' . implode(',', self::VALID_QUESTION_TYPES),
            ],
            'is_required' => [
                'nullable',
                'boolean',
            ],
            'settings' => [
                'nullable',
                'array',
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'options.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $settings = $this->buildQuestionSettings($request, $question->settings ?? []);

        $question->update([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->boolean('is_required'),
            'settings' => $settings,
        ]);

        $question->options()->delete();

        $this->saveOptions(
            $question,
            $validated['question_type'],
            $validated['options'] ?? []
        );

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(
        Event $event,
        Questionnaire $questionnaire,
        Question $question
    ): RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);
        $this->verifyQuestion($questionnaire, $question);

        $question->delete();

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Reorder questions in bulk.
     */
    public function reorder(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): JsonResponse|RedirectResponse {
        $this->verifyQuestionnaire($event, $questionnaire);

        $validated = $request->validate([
            'question_ids' => ['required', 'array'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        foreach ($validated['question_ids'] as $index => $id) {
            Question::where('id', $id)
                ->where('questionnaire_id', $questionnaire->id)
                ->update(['sort_order' => $index]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('events.questionnaires.show', [$event, $questionnaire])
            ->with('success', 'Question order updated.');
    }

    private function buildQuestionSettings(Request $request, array $existing = []): array
    {
        $inputSettings = $request->input('settings', []);

        $merged = array_merge($existing, is_array($inputSettings) ? $inputSettings : []);

        // Specific fields from request if supplied directly
        if ($request->has('placeholder')) {
            $merged['placeholder'] = trim((string) $request->input('placeholder'));
        }
        if ($request->has('help_text')) {
            $merged['help_text'] = trim((string) $request->input('help_text'));
        }
        if ($request->has('min_value') && $request->input('min_value') !== '') {
            $merged['min_value'] = (float) $request->input('min_value');
        }
        if ($request->has('max_value') && $request->input('max_value') !== '') {
            $merged['max_value'] = (float) $request->input('max_value');
        }
        if ($request->has('min_length') && $request->input('min_length') !== '') {
            $merged['min_length'] = (int) $request->input('min_length');
        }
        if ($request->has('max_length') && $request->input('max_length') !== '') {
            $merged['max_length'] = (int) $request->input('max_length');
        }
        if ($request->has('is_primary_identifier')) {
            $merged['is_primary_identifier'] = $request->boolean('is_primary_identifier');
        }
        if ($request->has('child_field_mapping')) {
            $merged['child_field_mapping'] = $request->input('child_field_mapping') ?: null;
        }
        if ($request->has('autofill_from_qr')) {
            $merged['autofill_from_qr'] = $request->boolean('autofill_from_qr');
        }
        if ($request->has('readonly_for_enumerator')) {
            $merged['readonly_for_enumerator'] = $request->boolean('readonly_for_enumerator');
        }

        return $merged;
    }

    private function saveOptions(
        Question $question,
        string $questionType,
        array $options
    ): void {
        $optionTypes = [
            'radio',
            'select',
            'checkbox',
            'poll',
        ];

        if (!in_array($questionType, $optionTypes, true)) {
            return;
        }

        foreach ($options as $index => $optionLabel) {
            $optionLabel = trim((string) $optionLabel);

            if ($optionLabel === '') {
                continue;
            }

            $question->options()->create([
                'option_label' => $optionLabel,
                'option_value' => null,
                'sort_order' => $index,
            ]);
        }
    }

    private function verifyQuestionnaire(
        Event $event,
        Questionnaire $questionnaire
    ): void {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );
    }

    private function verifyQuestion(
        Questionnaire $questionnaire,
        Question $question
    ): void {
        abort_unless(
            $question->questionnaire_id === $questionnaire->id,
            404
        );
    }
}