<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Čakáreň „Pripojiť sa k modlitbe" / odberu kanála od neprihlásených.
 * Označenie bez účtu už nezakladá riadok v `users` — uloží sa sem a do
 * `favorites` sa presunie až po kliknutí na odkaz z e-mailu
 * (Public\FavoriteConfirmationController). Nepotvrdené zmaže model:prune.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_favorites', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->index();
            $table->unsignedInteger('favorited_id');
            $table->string('favorited_type', 191);
            // sha256 tokenu z odkazu; samotný token je len v e-maile.
            $table->char('token', 64)->nullable()->unique();
            $table->timestamp('sent_at')->nullable();
            // Jediná automatická pripomienka (pending:remind).
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->unique(['email', 'favorited_type', 'favorited_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_favorites');
    }
};
