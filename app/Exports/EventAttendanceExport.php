<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventAttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $attendances)
    {
    }

    public function collection(): Collection
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'Child Code',
            'Child Name',
            'Attendance Date',
            'Attendance Time',
            'Status',
            'Marked By',
            'Method',
        ];
    }

    public function map($attendance): array
    {
        $child = $attendance->child;
        $childName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $child?->first_name,
            $child?->middle_name,
            $child?->last_name,
        ]))));

        return [
            $this->safeText($child?->child_code),
            $this->safeText($childName),
            $attendance->attendance_date?->format('Y-m-d')
                ?? $attendance->attendance_time?->format('Y-m-d')
                ?? '',
            $attendance->attendance_time?->format('H:i:s') ?? '',
            ucfirst((string) $attendance->status),
            $this->safeText($attendance->markedByEnumerator?->name ?? $attendance->markedBy?->name ?? ''),
            ucfirst(str_replace('_', ' ', (string) $attendance->method)),
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
