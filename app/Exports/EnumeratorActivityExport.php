<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EnumeratorActivityExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $enumerators)
    {
    }

    public function collection(): Collection
    {
        return $this->enumerators;
    }

    public function headings(): array
    {
        return [
            'Enumerator Code',
            'Enumerator Name',
            'Assigned Forms',
            'Total Submissions',
            "Today's Submissions",
            'Pending Review',
            'Approved Submissions',
            'First Activity',
            'Latest Activity',
        ];
    }

    public function map($enumerator): array
    {
        return [
            $this->safeText($enumerator->enumerator_code),
            $this->safeText($enumerator->name),
            (int) $enumerator->questionnaires_count,
            (int) $enumerator->total_submissions,
            (int) $enumerator->today_submissions,
            (int) $enumerator->pending_submissions,
            (int) $enumerator->approved_submissions,
            $enumerator->first_activity ?? '',
            $enumerator->latest_activity ?? '',
        ];
    }

    private function safeText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'{$value}"
            : $value;
    }
}
