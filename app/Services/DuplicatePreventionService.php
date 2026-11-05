<?php

namespace App\Services;

use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;

class DuplicatePreventionService
{
    /**
     * Compute normalized primary identifier string and hash for a submission.
     *
     * @param Questionnaire $questionnaire
     * @param array<string, mixed> $answers Keyed by question_{id} or {id}
     * @return array{has_identifier: bool, value: ?string, hash: ?string}
     */
    public function extractIdentifier(Questionnaire $questionnaire, array $answers): array
    {
        $settings = $questionnaire->primary_identifier_settings ?? [];
        $questionIds = $settings['question_ids'] ?? [];

        // If no question_ids explicitly in questionnaire settings, check questions with is_primary_identifier setting
        if (empty($questionIds)) {
            $questionIds = $questionnaire->questions
                ->filter(fn ($q) => $q->isPrimaryIdentifier())
                ->pluck('id')
                ->all();
        }

        if (empty($questionIds)) {
            return [
                'has_identifier' => false,
                'value' => null,
                'hash' => null,
            ];
        }

        $parts = [];
        $hasAnyValue = false;

        foreach ($questionIds as $qId) {
            $rawVal = $answers['question_' . $qId] ?? $answers[$qId] ?? null;

            if (is_array($rawVal)) {
                $clean = implode(',', array_map(fn ($v) => mb_strtolower(trim((string) $v)), $rawVal));
            } else {
                $clean = mb_strtolower(trim((string) $rawVal));
            }

            if ($clean !== '') {
                $hasAnyValue = true;
            }

            $parts[] = "q{$qId}:{$clean}";
        }

        if (!$hasAnyValue) {
            // All configured identifier fields were left blank
            return [
                'has_identifier' => false,
                'value' => null,
                'hash' => null,
            ];
        }

        $scope = $settings['scope'] ?? 'questionnaire'; // 'questionnaire' or 'event' or 'global'
        $scopePrefix = match ($scope) {
            'event' => "event:{$questionnaire->event_id}|",
            'global' => "global|",
            default => "quest:{$questionnaire->id}|",
        };

        $identifierValue = implode(' | ', $parts);
        $normalizedString = $scopePrefix . implode('|', $parts);
        $identifierHash = hash('sha256', $normalizedString);

        return [
            'has_identifier' => true,
            'value' => $identifierValue,
            'hash' => $identifierHash,
        ];
    }

    /**
     * Check if a duplicate submission exists.
     *
     * @param Questionnaire $questionnaire
     * @param ?string $identifierHash
     * @param ?int $excludeResponseId
     * @return bool
     */
    public function isDuplicate(
        Questionnaire $questionnaire,
        ?string $identifierHash,
        ?int $excludeResponseId = null
    ): bool {
        if (!$identifierHash) {
            return false;
        }

        $query = QuestionnaireResponse::query()
            ->where('primary_identifier_hash', $identifierHash)
            ->where('review_status', '!=', 'rejected');

        if ($excludeResponseId) {
            $query->where('id', '!=', $excludeResponseId);
        }

        return $query->exists();
    }
}
