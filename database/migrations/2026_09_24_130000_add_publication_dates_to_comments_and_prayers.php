<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dateTime('published_datetime')->nullable();
        });
        Schema::table('prayers', function (Blueprint $table) {
            $table->dateTime('published')->nullable();
        });

        // Jednorazové doplnenie starých dát; nové záznamy publikujeme bez posunu.
        $nextDay = DB::raw(DB::getDriverName() === 'sqlite'
            ? "datetime(coalesce(created_at, updated_at, CURRENT_TIMESTAMP), '+1 day')"
            : 'DATE_ADD(COALESCE(created_at, updated_at, CURRENT_TIMESTAMP), INTERVAL 1 DAY)');

        DB::table('comments')->where('published', 1)->update(['published_datetime' => $nextDay]);
        DB::table('prayers')->update(['published' => $nextDay]);

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('published');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('published_datetime', 'published');
        });

        // fulfilled_at aj jeho index ostávajú: vypočutie je nezávislé od publikovania.
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('published_boolean')->default(true);
        });
        DB::table('comments')->whereNull('published')->update(['published_boolean' => false]);
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('published');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('published_boolean', 'published');
        });
        Schema::table('prayers', function (Blueprint $table) {
            $table->dropColumn('published');
        });
    }
};
