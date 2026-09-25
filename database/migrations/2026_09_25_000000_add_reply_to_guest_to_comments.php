<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Odpoveď na komentár hosťa — neregistrovaného autora (napr. z YouTube),
        // na ktorú treba zareagovať aj mimo webu. Po vybavení sa príznak zruší.
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('reply_to_guest')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('reply_to_guest');
        });
    }
};
