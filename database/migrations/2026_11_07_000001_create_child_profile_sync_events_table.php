<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_profile_sync_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_uuid')->unique();
            $table->foreignId('child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('payload_hash', 64);
            $table->string('status', 24);
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profile_sync_events');
    }
};
