<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->uuid('event_uuid')->nullable()->unique();
            $table->string('device_id', 100)->nullable();
            $table->date('attendance_date')->nullable()->index();
            $table->timestamp('client_attended_at')->nullable();
            $table->char('sync_payload_hash', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_event_uuid_unique');
            $table->dropIndex(['attendance_date']);
            $table->dropColumn([
                'event_uuid',
                'device_id',
                'attendance_date',
                'client_attended_at',
                'sync_payload_hash',
            ]);
        });
    }
};