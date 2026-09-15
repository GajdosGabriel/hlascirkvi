<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zrušenie adresára kontaktov (/user/{user}/address).
 *
 * Model App\Models\AddresBook, UserAddressController, view
 * profiles.import_contacts aj položka "Moje kontakty" v menu sú zmazané -
 * tabuľku nemá čo čítať ani plniť.
 *
 * POZOR - `down()` vráti štruktúru, nie dáta. Riadky sa mažú natrvalo.
 * Pred nasadením na produkcii si urobte zálohu:
 *   mysqldump -u <user> -p <db> addres_books > addres_books.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('addres_books');
    }

    /**
     * Slúži len na to, aby sa dala migrácia odrolovať v lokálnom prostredí
     * bez pádu. Obsah tabuľky nevracia.
     */
    public function down(): void
    {
        if (Schema::hasTable('addres_books')) {
            return;
        }

        Schema::create('addres_books', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
