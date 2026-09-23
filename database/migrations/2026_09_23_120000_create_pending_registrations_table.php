<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Čakáreň registrácií. Formulár /register už nezakladá riadok v `users`
 * (a s ním kanál), ale len záznam sem. Skutočný účet vznikne až po kliknutí
 * na odkaz z e-mailu (App\Http\Controllers\Auth\RegisterController::confirm).
 * Nepotvrdené záznamy po expirácii zmaže model:prune (App\Models\PendingRegistration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            // Už zahashované heslo — v čistej podobe sa nikam neukladá.
            $table->string('password', 191);
            // sha256 tokenu z odkazu; samotný token je len v e-maile.
            $table->char('token', 64)->nullable()->unique();
            $table->unsignedSmallInteger('send_count')->default(1);
            $table->timestamp('sent_at')->nullable();
            // Jediná automatická pripomienka (registrations:remind).
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
