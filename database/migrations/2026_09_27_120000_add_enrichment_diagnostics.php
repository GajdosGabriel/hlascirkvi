<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canal_enrichments', function (Blueprint $table) {
            $table->json('diagnostics')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('canal_enrichments', function (Blueprint $table) {
            $table->dropColumn('diagnostics');
        });
    }
};
