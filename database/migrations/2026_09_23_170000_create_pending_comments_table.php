<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Čakáreň komentárov od neprihlásených (a od účtov s neoverenou adresou).
 * Komentár bez registrácie už nezakladá účet v `users` — uloží sa sem a do
 * `comments` sa presunie až po kliknutí na odkaz z e-mailu
 * (Public\CommentConfirmationController). Nepotvrdené zmaže model:prune.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_comments', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->index();
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->text('body');
            // sha256 tokenu z odkazu; samotný token je len v e-maile.
            $table->char('token', 64)->nullable()->unique();
            $table->timestamp('sent_at')->nullable();
            // Jediná automatická pripomienka (pending:remind).
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_comments');
    }
};
