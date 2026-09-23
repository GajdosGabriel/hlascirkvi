<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Čakáreň modlitieb od neprihlásených. Modlitba bez registrácie už nezakladá
 * účet v `users` — uloží sa sem a do `prayers` (s účtom a kanálom) sa presunie
 * až po kliknutí na odkaz z e-mailu (Public\PrayerController::confirm).
 * Nepotvrdené záznamy po expirácii zmaže model:prune (App\Models\PendingPrayer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_prayers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->index();
            $table->string('title', 255);
            $table->text('body');
            $table->string('user_name', 255)->nullable();
            // sha256 tokenu z odkazu; samotný token je len v e-maile.
            $table->char('token', 64)->nullable()->unique();
            $table->timestamp('sent_at')->nullable();
            // Jediná automatická pripomienka (prayers:remind).
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_prayers');
    }
};
