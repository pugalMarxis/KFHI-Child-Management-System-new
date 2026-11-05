<?php

namespace App\Exports;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuestionnaireResponsesExport implements FromCollection, WithHeadings, WithMapping
{
    private array $questions;

    public function __construct(
        private Questionnaire $questionnaire,
        private Collection $responses
    ) {
        $this->questions = $this->questionnaire->questions()->orderBy('sort_order')->get()->all();
    }

    public function collection(): Collection
    {
        return $this->responses;
    }

    public function headings(): array
    {
        $headers = [
            'Submission ID',
            'Submission Date',
            'Review Status',
            'Merge Status',
            'Enumerator ID',
            'Enumerator Name',
            'Child Code',
            'Primary Identifier',
        ];

        foreach ($this->questions as $question) {
            $headers[] = $this->sanitizeForExcel($question->question_text);
        }

        return $headers;
    }

    public function map($response): array
    {
        $row = [
            $response->id,
            $response->submitted_at?->format('Y-m-d H:i:s'),
            ucfirst($response->review_status),
            ucfirst($response->merge_status),
            $response->enumerator?->enumerator_code ?? 'N/A',
            $this->sanitizeForExcel($response->enumerator_name ?? 'N/A'),
            $response->child?->child_code ?? ($response->child_snapshot['child_code'] ?? 'N/A'),
            $this->sanitizeForExcel($response->primary_identifier_value ?? 'N/A'),
        ];

        foreach ($this->questions as $question) {
            $raw = $response->getAnswerForQuestion($question->id);

            if (is_array($raw)) {
                $val = implode(', ', $raw);
            } else {
                $val = $raw !== null ? (string) $raw : '';
            }

            $row[] = $this->sanitizeForExcel($val);
        }

        return $row;
    }

    /**
     * Prevent CSV / Spreadsheet Formula Injection.
     */
    private function sanitizeForExcel(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $firstChar = $value[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
