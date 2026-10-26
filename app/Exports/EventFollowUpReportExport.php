
<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EventFollowUpReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Event Code',
            'Event Title',
            'Follow-up Title',
            'Assigned To',
            'Due Date',
            'Priority',
            'Status',
            'Reason',
            'Description',
            'Outcome',
        ];
    }

    public function map($followUp): array
    {
        return [
            $followUp->id,
            $followUp->event?->event_code ?? '',
            $followUp->event?->title ?? '',
            $followUp->title,
            $followUp->assignee?->name ?? '',
            $followUp->due_date?->format('Y-m-d') ?? '',
            $followUp->priority ?? '',
            $followUp->status ?? '',
            $followUp->reason ?? '',
            $followUp->description ?? '',
            $followUp->outcome ?? '',
        ];
    }
}
