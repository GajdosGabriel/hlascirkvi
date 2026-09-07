<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vlastné počítadlo zobrazení namiesto balíka cyrildewit/eloquent-viewable.
 *
 * Pôvodná tabuľka balíka viazala návštevníka cookie (`visitor`) a ukladala
 * presný čas (`viewed_at`). Nová ho viaže pseudonymom, ktorý sa každý deň mení
 * (VisitorPseudonym), a drží len dátum — z tabuľky sa teda nedá poskladať, čo
 * konkrétny človek čítal naprieč dňami, a nepotrebuje na to cookie.
 *
 * Tabuľka slúži na rozpoznanie opakovaného zobrazenia, nie ako archív: trvalé
 * číslo žije v denormalizovanom `posts.count_view`, takže mazanie starých
 * riadkov (app:views-prune) o počet nepripraví. Preto sa stará tabuľka zahadzuje
 * aj s obsahom — 37 riadkov z dvoch dní, ktoré v novej schéme nemajú význam,
 * lebo cookie `visitor` sa na denný pseudonym prepočítať nedá.
 *
 * Unikátny index cez všetky štyri stĺpce je zároveň pravidlo „jeden návštevník
 * = jedno zobrazenie za deň": zápis ide cez insertOrIgnore a `count_view` sa
 * zvýši len vtedy, keď riadok naozaj pribudol.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('views');

        Schema::create('views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('viewable_type', 60);
            $table->unsignedInteger('viewable_id');
            $table->char('visitor_hash', 64);
            $table->date('viewed_on');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['viewable_type', 'viewable_id', 'visitor_hash', 'viewed_on'],
                'views_unique_per_day',
            );
            // „Koľko zobrazení za posledných N dní" — filter Trend aj
            // admin štatistika čítajú presne týmto poradím stĺpcov.
            $table->index(['viewable_type', 'viewable_id', 'viewed_on'], 'views_target_day_index');
            // Mazanie starých riadkov.
            $table->index('viewed_on', 'views_viewed_on_index');
        });
    }

    /**
     * Vráti schému balíka, nie obsah. Slúži len na to, aby sa dala migrácia
     * odrolovať v lokálnom prostredí bez pádu.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');

        Schema::create('views', function (Blueprint $table) {
            $table->increments('id');
            $table->string('viewable_type');
            $table->unsignedBigInteger('viewable_id');
            $table->text('visitor')->nullable();
            $table->string('collection')->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['viewable_type', 'viewable_id'], 'views_viewable_type_viewable_id_index');
        });
    }
};
