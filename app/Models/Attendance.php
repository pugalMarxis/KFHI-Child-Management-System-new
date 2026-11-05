<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'event_id',
        'marked_by',
        'attendance_time',
        'method',
        'status',
        'remarks',
        'attendance_date',
        'event_uuid',
        'device_id',
        'client_attended_at',
        'sync_payload_hash',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'client_attended_at' => 'datetime',
            'attendance_time' => 'datetime'
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
