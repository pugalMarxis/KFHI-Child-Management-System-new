<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            // Drop unique constraint to allow public forms and longitudinal/multiple responses
            if (DB::getDriverName() === 'mysql') {
                // Drop foreign keys before dropping unique index in MySQL
                $table->dropForeign(['questionnaire_id']);
                $table->dropForeign(['child_id']);
                $table->dropUnique('questionnaire_child_unique');

                // Restore foreign keys with nullable child_id
                $table->unsignedBigInteger('child_id')->nullable()->change();
                $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->cascadeOnDelete();
                $table->foreign('child_id')->references('id')->on('children')->nullOnDelete();
            } else {
                // SQLite supports nullable change and dropping unique index
                try {
                    $table->dropUnique('questionnaire_child_unique');
                } catch (\Throwable $e) {
                    // Ignore if already dropped or absent
                }
                $table->unsignedBigInteger('child_id')->nullable()->change();
            }

            if (!Schema::hasColumn('questionnaire_responses', 'enumerator_id')) {
                $table->foreignId('enumerator_id')
                    ->nullable()
                    ->after('child_id')
                    ->constrained('enumerators')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('questionnaire_responses', 'enumerator_name')) {
                $table->string('enumerator_name', 255)->nullable()->after('enumerator_id');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'primary_identifier_value')) {
                $table->string('primary_identifier_value', 255)->nullable()->after('enumerator_name');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'primary_identifier_hash')) {
                $table->string('primary_identifier_hash', 64)->nullable()->index()->after('primary_identifier_value');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('primary_identifier_hash');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'review_status')) {
                $table->string('review_status', 30)->default('submitted')->index()->after('started_at');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'reviewed_by')) {
                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->after('review_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('questionnaire_responses', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_at');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'merge_status')) {
                $table->string('merge_status', 30)->default('pending')->index()->after('review_notes');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'merged_by')) {
                $table->foreignId('merged_by')
                    ->nullable()
                    ->after('merge_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('questionnaire_responses', 'merged_at')) {
                $table->timestamp('merged_at')->nullable()->after('merged_by');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'child_snapshot')) {
                $table->json('child_snapshot')->nullable()->after('merged_at');
            }

            if (!Schema::hasColumn('questionnaire_responses', 'approved_fields')) {
                $table->json('approved_fields')->nullable()->after('child_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->dropForeign(['enumerator_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['merged_by']);

            $table->dropColumn([
                'enumerator_id',
                'enumerator_name',
                'primary_identifier_value',
                'primary_identifier_hash',
                'started_at',
                'review_status',
                'reviewed_by',
                'reviewed_at',
                'review_notes',
                'merge_status',
                'merged_by',
                'merged_at',
                'child_snapshot',
                'approved_fields',
            ]);
        });
    }
};
