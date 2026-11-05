<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FilteredResponseExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private Collection $responses,
        private bool $childOnly = false
    ) {
    }

    public function collection(): Collection
    {
        return $this->responses;
    }

    public function headings(): array
    {
        if ($this->childOnly) {
            return [
                'Submission ID',
                'Questionnaire',
                'Event',
                'Child Code',
                'Child Full Name',
                'Primary Identifier',
                'Gender',
                'Date of Birth',
                'Address',
                'District',
                'GN Division',
                'School Name',
                'Grade',
                'Review Status',
                'Merge Status',
                'Submission Date',
                'Approved Fields',
                'Review Notes',
            ];
        }

        return [
            'Submission ID',
            'Questionnaire',
            'Event',
            'Child Code',
            'Child Full Name',
            'Primary Identifier',
            'Enumerator Code',
            'Enumerator Name',
            'Gender',
            'Date of Birth',
            'Address',
            'District',
            'GN Division',
            'School Name',
            'Grade',
            'Review Status',
            'Merge Status',
            'Submission Date',
            'Approved Fields',
            'Review Notes',
        ];
    }

    public function map($response): array
    {
        $child = $response->child;
        $snapshot = $response->child_snapshot ?? [];
        $fullName = trim(implode(' ', array_filter([
            $child?->first_name ?? $snapshot['first_name'] ?? null,
            $child?->middle_name ?? $snapshot['middle_name'] ?? null,
            $child?->last_name ?? $snapshot['last_name'] ?? null,
        ], fn($value) => $value !== null && $value !== '')));

        $approvedFields = is_array($response->approved_fields ?? null)
            ? implode(', ', array_map('strval', $response->approved_fields))
            : '';

        if ($this->childOnly) {
            return [
                $response->id,
                $response->questionnaire?->title ?? 'N/A',
                $response->questionnaire?->event?->title ?? 'N/A',
                $child?->child_code ?? ($snapshot['child_code'] ?? 'N/A'),
                $this->sanitizeForExcel($fullName ?: ($snapshot['full_name'] ?? 'N/A')),
                $this->sanitizeForExcel($response->primary_identifier_value ?? 'N/A'),
                $child?->gender ?? ($snapshot['gender'] ?? ''),
                $child?->date_of_birth?->format('Y-m-d') ?? ($snapshot['date_of_birth'] ?? ''),
                $child?->address ?? ($snapshot['address'] ?? ''),
                $child?->district ?? ($snapshot['district'] ?? ''),
                $child?->gn_division ?? ($snapshot['gn_division'] ?? ''),
                $child?->school_name ?? ($snapshot['school_name'] ?? ''),
                $child?->grade ?? ($snapshot['grade'] ?? ''),
                ucfirst((string) $response->review_status),
                ucfirst((string) $response->merge_status),
                $response->submitted_at?->format('Y-m-d H:i:s') ?? '',
                $this->sanitizeForExcel($approvedFields),
                $this->sanitizeForExcel($response->review_notes ?? ''),
            ];
        }

        return [
            $response->id,
            $response->questionnaire?->title ?? 'N/A',
            $response->questionnaire?->event?->title ?? 'N/A',
            $child?->child_code ?? ($snapshot['child_code'] ?? 'N/A'),
            $this->sanitizeForExcel($fullName ?: ($snapshot['full_name'] ?? 'N/A')),
            $this->sanitizeForExcel($response->primary_identifier_value ?? 'N/A'),
            $response->enumerator?->enumerator_code ?? ($response->enumerator_name ? 'N/A' : 'Public'),
            $this->sanitizeForExcel($response->enumerator?->name ?? ($response->enumerator_name ?? 'Public Response')),
            $child?->gender ?? ($snapshot['gender'] ?? ''),
            $child?->date_of_birth?->format('Y-m-d') ?? ($snapshot['date_of_birth'] ?? ''),
            $child?->address ?? ($snapshot['address'] ?? ''),
            $child?->district ?? ($snapshot['district'] ?? ''),
            $child?->gn_division ?? ($snapshot['gn_division'] ?? ''),
            $child?->school_name ?? ($snapshot['school_name'] ?? ''),
            $child?->grade ?? ($snapshot['grade'] ?? ''),
            ucfirst((string) $response->review_status),
            ucfirst((string) $response->merge_status),
            $response->submitted_at?->format('Y-m-d H:i:s') ?? '',
            $this->sanitizeForExcel($approvedFields),
            $this->sanitizeForExcel($response->review_notes ?? ''),
        ];
    }

    private function sanitizeForExcel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $string = is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
        $firstChar = $string[0];

        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $string;
        }

        return $string;
    }
}
