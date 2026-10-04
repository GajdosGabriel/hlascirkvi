<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            // Existujúce semináre zostávajú podujatiami aj po nasadení.
            $table->string('kind', 20)->default('seminar')->index();
        });
    }

    public function down(): void
    {
        Schema::table('seminars', fn (Blueprint $table) => $table->dropColumn('kind'));
    }
};
