
<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ChildFollowUpReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $followUps)
    {
    }

    public function collection(): Collection
    {
        return $this->followUps;
    }

    public function headings(): array
    {
        return [
            'Follow-up ID',
            'Child Code',
            'Child Name',
            'Category',
            'Follow-up Date',
            'Next Follow-up Date',
            'Assigned To',
            'Priority',
            'Status',
            'Description',
            'Action Taken',
        ];
    }

    public function map($followUp): array
    {
        $child = $followUp->child;

        return [
            $followUp->id,
            $child?->child_code ?? '',
            trim(($child?->first_name ?? '') . ' ' . ($child?->last_name ?? '')),
            $followUp->category ?? '',
            $followUp->follow_up_date?->format('Y-m-d') ?? '',
            $followUp->next_follow_up_date?->format('Y-m-d') ?? '',
            $followUp->assignedTo?->name ?? '',
            $followUp->priority ?? '',
            $followUp->status ?? '',
            $followUp->description ?? '',
            $followUp->action_taken ?? '',
        ];
    }
}
