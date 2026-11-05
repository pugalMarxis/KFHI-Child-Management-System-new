<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Child;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChildRecordMergeService
{
    /**
     * Permitted child table columns for merge.
     */
    public const PERMITTED_CHILD_FIELDS = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'school_name',
        'grade',
        'address',
        'district',
        'gn_division',
        'notes',
    ];

    /**
     * Compare a submitted response against the current child record.
     *
     * @param QuestionnaireResponse $response
     * @return array<string, array{field: string, label: string, current: mixed, submitted: mixed, differs: bool, eligible: bool}>
     */
    public function buildComparison(QuestionnaireResponse $response): array
    {
        $child = $response->child;
        $questionnaire = $response->questionnaire;
        $mapping = $questionnaire->child_field_mapping ?? [];

        // Build a lookup of mapped fields: child_field => question_id
        $fieldToQuestion = [];
        foreach ($mapping as $item) {
            if (!empty($item['child_field']) && !empty($item['question_id'])) {
                $fieldToQuestion[$item['child_field']] = (int) $item['question_id'];
            }
        }

        // Also check question settings for mapped fields
        foreach ($questionnaire->questions as $question) {
            $mapped = $question->mappedChildField();
            if ($mapped && !isset($fieldToQuestion[$mapped])) {
                $fieldToQuestion[$mapped] = $question->id;
            }
        }

        $comparison = [];

        foreach (self::PERMITTED_CHILD_FIELDS as $field) {
            $questionId = $fieldToQuestion[$field] ?? null;
            $submittedVal = $questionId ? $response->getAnswerForQuestion($questionId) : null;

            if ($submittedVal === null && !isset($fieldToQuestion[$field])) {
                continue;
            }

            $currentVal = null;
            if ($child) {
                if ($field === 'date_of_birth' && $child->date_of_birth) {
                    $currentVal = $child->date_of_birth->format('Y-m-d');
                } else {
                    $currentVal = $child->{$field};
                }
            }

            $differs = (string) $currentVal !== (string) $submittedVal;

            $comparison[$field] = [
                'field' => $field,
                'label' => ucwords(str_replace('_', ' ', $field)),
                'current' => $currentVal,
                'submitted' => $submittedVal,
                'differs' => $differs,
                'eligible' => true,
            ];
        }

        return $comparison;
    }

    /**
     * Merge reviewer-approved field updates into the child record.
     *
     * @param QuestionnaireResponse $response
     * @param User $reviewer
     * @param array<string> $approvedFields
     * @param ?string $notes
     * @param ?string $ipAddress
     * @param ?string $userAgent
     * @return Child
     * @throws ValidationException
     */
    public function mergeApprovedData(
        QuestionnaireResponse $response,
        User $reviewer,
        array $approvedFields,
        ?string $notes = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Child {
        if ($response->isMerged()) {
            throw ValidationException::withMessages([
                'merge' => 'This response has already been merged into the child record.',
            ]);
        }

        if (!$response->child_id) {
            throw ValidationException::withMessages([
                'merge' => 'This response is not associated with an existing child record.',
            ]);
        }

        return DB::transaction(function () use (
            $response,
            $reviewer,
            $approvedFields,
            $notes,
            $ipAddress,
            $userAgent
        ) {
            // Lock and reload the child record to prevent race conditions
            $child = Child::where('id', $response->child_id)->lockForUpdate()->firstOrFail();

            $comparison = $this->buildComparison($response);
            $changesToApply = [];
            $auditBefore = [];
            $auditAfter = [];

            foreach ($approvedFields as $field) {
                if (!in_array($field, self::PERMITTED_CHILD_FIELDS, true)) {
                    continue; // Skip any fields not explicitly permitted
                }

                if (!isset($comparison[$field])) {
                    continue;
                }

                $newVal = $comparison[$field]['submitted'];
                $oldVal = $child->{$field};

                if ($field === 'date_of_birth' && $child->date_of_birth) {
                    $oldVal = $child->date_of_birth->format('Y-m-d');
                }

                $changesToApply[$field] = $newVal;
                $auditBefore[$field] = $oldVal;
                $auditAfter[$field] = $newVal;
            }

            if (!empty($changesToApply)) {
                $child->update($changesToApply);
            }

            // Update the response staging status to preserve historical integrity
            $response->update([
                'review_status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes ?: $response->review_notes,
                'merge_status' => 'merged',
                'merged_by' => $reviewer->id,
                'merged_at' => now(),
                'approved_fields' => array_values($approvedFields),
            ]);

            // Audit log recording
            AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'merge_child_survey',
                'module' => 'questionnaire_responses',
                'record_id' => $response->id,
                'description' => sprintf(
                    'Merged %d approved fields from response #%d into child %s (%s). Changes: %s',
                    count($changesToApply),
                    $response->id,
                    $child->child_code,
                    $child->full_name,
                    json_encode(['before' => $auditBefore, 'after' => $auditAfter])
                ),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $child;
        });
    }
}
