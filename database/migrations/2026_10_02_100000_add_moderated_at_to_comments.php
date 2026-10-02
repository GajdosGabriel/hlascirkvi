<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Kedy komentár naposledy prešiel moderáciou. NULL = ešte nie;
            // nočný príkaz berie len takéto riadky.
            $table->timestamp('moderated_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('comments', fn (Blueprint $table) => $table->dropColumn('moderated_at'));
    }
};
