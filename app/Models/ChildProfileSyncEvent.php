<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildProfileSyncEvent extends Model
{
    protected $fillable = [
        'operation_uuid',
        'child_id',
        'user_id',
        'payload_hash',
        'status',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
        ];
    }
}
