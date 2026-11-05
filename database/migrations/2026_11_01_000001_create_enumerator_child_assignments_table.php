<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enumerator_child_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enumerator_id')
                ->constrained('enumerators')
                ->cascadeOnDelete();

            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            $table->foreignId('allowed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['enumerator_id', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enumerator_child_assignments');
    }
};