<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->string('identity_mode', 32)->default('organization')->index();
        });

        // Query builder zahŕňa aj skryté a mäkko zmazané kanály.
        DB::table('canals')->where('type', 'personal')->update(['identity_mode' => 'personal']);

        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex('canals_front_list_index');
            $table->dropIndex(['type']);
            $table->dropColumn('type');
            $table->index(['front_listed_at', 'identity_mode'], 'canals_front_list_index');
        });
    }

    public function down(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->index();
        });

        DB::table('canals')->update(['type' => DB::raw("CASE WHEN identity_mode = 'personal' THEN 'personal' ELSE 'organization' END")]);

        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex('canals_front_list_index');
            $table->dropIndex(['identity_mode']);
            $table->dropColumn('identity_mode');
            $table->index(['front_listed_at', 'type'], 'canals_front_list_index');
        });
    }
};
