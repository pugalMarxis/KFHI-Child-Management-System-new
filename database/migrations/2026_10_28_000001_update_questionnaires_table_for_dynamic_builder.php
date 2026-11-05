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
        Schema::table('questionnaires', function (Blueprint $table) {
            if (!Schema::hasColumn('questionnaires', 'public_token')) {
                $table->string('public_token', 64)->nullable()->unique()->after('status');
            }

            if (!Schema::hasColumn('questionnaires', 'primary_identifier_settings')) {
                $table->json('primary_identifier_settings')->nullable()->after('public_token');
            }

            if (!Schema::hasColumn('questionnaires', 'child_field_mapping')) {
                $table->json('child_field_mapping')->nullable()->after('primary_identifier_settings');
            }

            if (!Schema::hasColumn('questionnaires', 'requires_enumerator')) {
                $table->boolean('requires_enumerator')->default(false)->after('child_field_mapping');
            }

            if (!Schema::hasColumn('questionnaires', 'allow_anonymous')) {
                $table->boolean('allow_anonymous')->default(true)->after('requires_enumerator');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'public_token',
                'primary_identifier_settings',
                'child_field_mapping',
                'requires_enumerator',
                'allow_anonymous',
            ]);
        });
    }
};
