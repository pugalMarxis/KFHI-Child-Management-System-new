<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Services\ChildRecordMergeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function index(Event $event): View
    {
        $questionnaires = Questionnaire::where('event_id', $event->id)
            ->withCount(['questions', 'responses'])
            ->latest()
            ->get();

        return view(
            'admin.events.questionnaires.index',
            compact('event', 'questionnaires')
        );
    }

    public function create(Event $event): View
    {
        return view(
            'admin.events.questionnaires.create',
            compact('event')
        );
    }

    public function store(
        Request $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('events.questionnaires.show', [$event, $questionnaire])
            ->with('success', 'Questionnaire created. Now add questions to build your form.');
    }

    public function show(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $questionnaire->load([
            'questions.options',
            'responses',
            'enumerators',
        ]);

        $availableChildFields = [
            'child_code' => 'Child ID / Code (KFHI Code)',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'school_name' => 'School Name',
            'grade' => 'Grade / Class',
            'district' => 'District',
            'gn_division' => 'GN Division',
            'address' => 'Home Address',
            'notes' => 'Notes / Observation',
        ];

        $responseStats = [
            'total' => $questionnaire->responses->count(),
            'pending' => $questionnaire->responses->whereIn('review_status', ['submitted', 'under_review'])->count(),
            'approved' => $questionnaire->responses->where('review_status', 'approved')->count(),
            'merged' => $questionnaire->responses->where('merge_status', 'merged')->count(),
            'rejected' => $questionnaire->responses->where('review_status', 'rejected')->count(),
        ];

        return view(
            'admin.events.questionnaires.show',
            compact('event', 'questionnaire', 'availableChildFields', 'responseStats')
        );
    }

    public function edit(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        return view(
            'admin.events.questionnaires.edit',
            compact('event', 'questionnaire')
        );
    }

    public function update(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:draft,published,closed',
            ],
            'requires_enumerator' => ['nullable', 'boolean'],
            'allow_anonymous' => ['nullable', 'boolean'],
        ]);

        if ($validated['status'] === 'published') {
            $questionnaire->ensurePublicToken();
        }

        $questionnaire->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'requires_enumerator' => $request->boolean('requires_enumerator', false),
            'allow_anonymous' => $request->boolean('allow_anonymous', true),
        ]);

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Questionnaire updated successfully.');
    }

    public function publish(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        if ($questionnaire->questions()->count() === 0) {
            return redirect()
                ->back()
                ->with('error', 'Add at least one question before publishing the questionnaire.');
        }

        $questionnaire->ensurePublicToken();
        $questionnaire->update(['status' => 'published']);

        return redirect()
            ->route('events.questionnaires.show', [$event, $questionnaire])
            ->with('success', 'Questionnaire published successfully! Public share link has been generated.');
    }

    public function updateSettings(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $validated = $request->validate([
            'primary_question_ids' => ['nullable', 'array'],
            'primary_question_ids.*' => ['integer'],
            'primary_scope' => ['nullable', 'in:questionnaire,event,global'],
            'requires_enumerator' => ['nullable', 'boolean'],
            'allow_anonymous' => ['nullable', 'boolean'],
            'mappings' => ['nullable', 'array'],
            'mappings.*.child_field' => ['nullable', 'string', 'max:50'],
            'mappings.*.question_id' => ['nullable', 'integer'],
            'mappings.*.autofill' => ['nullable', 'boolean'],
            'mappings.*.readonly_for_enumerator' => ['nullable', 'boolean'],
        ]);

        // Primary identifier settings
        $primarySettings = [
            'question_ids' => array_map('intval', $validated['primary_question_ids'] ?? []),
            'scope' => $validated['primary_scope'] ?? 'questionnaire',
        ];

        // Process child mappings
        $cleanMappings = [];
        if (!empty($validated['mappings'])) {
            foreach ($validated['mappings'] as $item) {
                if (!empty($item['child_field']) && !empty($item['question_id'])) {
                    $cleanMappings[] = [
                        'child_field' => trim($item['child_field']),
                        'question_id' => (int) $item['question_id'],
                        'autofill' => !empty($item['autofill']),
                        'readonly_for_enumerator' => !empty($item['readonly_for_enumerator']),
                    ];
                }
            }
        }

        $questionnaire->update([
            'primary_identifier_settings' => $primarySettings,
            'child_field_mapping' => $cleanMappings,
            'requires_enumerator' => $request->boolean('requires_enumerator', false),
            'allow_anonymous' => $request->boolean('allow_anonymous', true),
        ]);

        return redirect()
            ->route('events.questionnaires.show', [$event, $questionnaire])
            ->with('success', 'Form settings, duplicate prevention rules, and child autofill mappings saved.');
    }

    public function destroy(
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $questionnaire->delete();

        return redirect()
            ->route('events.questionnaires.index', $event)
            ->with('success', 'Questionnaire deleted successfully.');
    }
}