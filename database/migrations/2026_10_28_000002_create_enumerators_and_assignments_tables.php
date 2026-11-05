<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('enumerators')) {
            Schema::create('enumerators', function (Blueprint $table) {
                $table->id();
                $table->string('enumerator_code', 30)->unique();
                $table->string('name', 255);
                $table->string('phone', 30)->nullable();
                $table->string('pin_code', 60); // Hashed 4-6 digit individual PIN for verification
                $table->string('status', 20)->default('active')->index();
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('enumerator_questionnaire')) {
            Schema::create('enumerator_questionnaire', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enumerator_id')
                    ->constrained('enumerators')
                    ->cascadeOnDelete();
                $table->foreignId('questionnaire_id')
                    ->constrained('questionnaires')
                    ->cascadeOnDelete();
                $table->timestamp('assigned_at')->useCurrent();

                $table->unique(['enumerator_id', 'questionnaire_id'], 'enum_quest_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enumerator_questionnaire');
        Schema::dropIfExists('enumerators');
    }
};
