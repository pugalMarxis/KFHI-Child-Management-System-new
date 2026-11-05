<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enumerator_child_assignments')
            && !Schema::hasColumn('enumerator_child_assignments', 'include_family_details')) {
            Schema::table('enumerator_child_assignments', function (Blueprint $table) {
                $table->boolean('include_family_details')->default(false)->after('allowed_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enumerator_child_assignments')
            && Schema::hasColumn('enumerator_child_assignments', 'include_family_details')) {
            Schema::table('enumerator_child_assignments', function (Blueprint $table) {
                $table->dropColumn('include_family_details');
            });
        }
    }
};
