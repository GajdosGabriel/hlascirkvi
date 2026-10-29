<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Podpora — prihlásený používateľ napíše správcom webu a odpoveď dostane
 * v nástenke (Podpora) aj e-mailom. Rovnaký model ako na portáli tixio.eu.
 *
 * Do 10/2026 formulár „Napíšte nám" v päte len poslal správcovi e-mail
 * (riadok v `messengers`). Odpovedalo sa z osobnej schránky, takže sa nedalo
 * dohľadať, čo kto napísal a či to niekto vybavil. Staré riadky sa sem
 * neprenášajú: anonymný formulár ich ukladal s odosielateľom = správca
 * (user 1), takže odosielateľ nie je známy — a väčšinou išlo o spam.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('subject', 150);
            $table->string('status', 16)->default('open')->index();
            // Kde človek písal — pri „nejde mi to" to rozhoduje.
            $table->string('page_url', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            // Kedy strana naposledy videla vlákno; null = čaká na ňu niečo nové.
            $table->timestamp('user_seen_at')->nullable();
            $table->timestamp('staff_seen_at')->nullable();
            $table->timestamps();

            // users má `increments` (unsigned int), nie bigint — preto ručne.
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('user_id')->nullable();
            // Príznak, nie odvodenie z roly: správca, ktorý píše o vlastnom
            // probléme, je v tom vlákne používateľ.
            $table->boolean('is_staff')->default(false);
            $table->text('body');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
