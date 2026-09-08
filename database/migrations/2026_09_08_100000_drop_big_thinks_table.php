<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zrušenie "veľkých myšlienok" k videám.
 *
 * Model App\Models\BigThink, trait HasBigThink, PostThingController,
 * BigThingsRequest, observer, notifikácia, blade formulár aj Vue komponent
 * sú zmazané - tabuľku nemá čo čítať ani plniť.
 *
 * POZOR - `down()` vráti štruktúru, nie dáta. Riadky sa mažú natrvalo.
 * Pred nasadením na produkcii si urobte zálohu:
 *   mysqldump -u <user> -p <db> big_thinks > big_thinks.sql
 *
 * Kontrola tabuľky je tu preto, že migrácia vytvárajúca big_thinks
 * v repozitári nie je - na prázdnej databáze nie je čo mazať.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('big_thinks');
    }

    /**
     * Slúži len na to, aby sa dala migrácia odrolovať v lokálnom prostredí
     * bez pádu. Obsah tabuľky nevracia.
     */
    public function down(): void
    {
        if (Schema::hasTable('big_thinks')) {
            return;
        }

        Schema::create('big_thinks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_id')->index();
            $table->unsignedInteger('post_id');
            $table->text('body');
            $table->boolean('published')->default(true);
            $table->boolean('blocked')->default(false);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }
};
