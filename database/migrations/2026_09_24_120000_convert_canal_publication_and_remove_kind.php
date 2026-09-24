<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pomocný stĺpec zabráni priamemu pretypovaniu booleanu na neplatný dátum.
        Schema::table('canals', function (Blueprint $table) {
            $table->dateTime('published_datetime')->nullable();
        });

        DB::table('canals')->where('published', 1)->orderBy('id')
            ->chunkById(500, function ($canals) {
                foreach ($canals as $canal) {
                    DB::table('canals')->where('id', $canal->id)->update([
                        'published_datetime' => Carbon::parse($canal->created_at ?? $canal->updated_at ?? now())->addDay(),
                    ]);
                }
            });

        Schema::table('canals', function (Blueprint $table) {
            $table->dropColumn('published');
        });
        Schema::table('canals', function (Blueprint $table) {
            $table->renameColumn('published_datetime', 'published');
        });

        // Existujúci type je smerodajný; kind doplní iba chýbajúce zaradenie.
        DB::table('canals')->whereNull('type')->where('kind', 'person')->update(['type' => 'personal']);
        DB::table('canals')->whereNull('type')->where('kind', 'community')->update(['type' => 'organization']);

        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex('canals_front_list_index');
            $table->dropColumn('kind');
            $table->index(['front_listed_at', 'type'], 'canals_front_list_index');
        });
    }

    public function down(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->boolean('published_boolean')->default(true);
            $table->string('kind', 20)->nullable();
        });

        DB::table('canals')->whereNull('published')->update(['published_boolean' => false]);
        DB::table('canals')->where('type', 'personal')->update(['kind' => 'person']);
        DB::table('canals')->where('type', 'organization')->update(['kind' => 'community']);

        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex('canals_front_list_index');
            $table->dropColumn('published');
            $table->index(['front_listed_at', 'kind'], 'canals_front_list_index');
        });
        Schema::table('canals', function (Blueprint $table) {
            $table->renameColumn('published_boolean', 'published');
        });
    }
};
