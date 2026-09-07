<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Zrušenie lokálnej agendy podujatí.
 *
 * Podujatia sa už držia výhradne na portáli event.hlascirkvi.sk a čítame ich
 * cez API (App\Services\EventPortal). Model App\Models\Event, controllery,
 * prihlasovanie aj importéry sú zmazané, takže tieto tabuľky nemá čo čítať.
 *
 * POZOR — migrácia je nevratná, čo sa dát týka:
 *   - `down()` vytvorí tabuľky naspäť, ale prázdne;
 *   - polymorfné riadky (obrázky, komentáre, obľúbené, videnia) viazané na
 *     App\Models\Event sa mažú a nedajú sa vrátiť;
 *   - fyzické súbory obrázkov v storage/app/public migrácia NEMAŽE. Ak sa majú
 *     uvoľniť, treba ich zmazať zvlášť podľa zoznamu ciest, ktorý si vytiahnite
 *     PRED spustením:
 *       select url, thumb from images where fileable_type = 'App\\Models\\Event';
 *
 * Pred nasadením na produkcii si urobte zálohu tabuliek events,
 * event_subscribes, event_person a images.
 */
return new class extends Migration
{
    /** Polymorfné väzby, ktoré po zmazaní podujatí ostanú visieť naprázdno. */
    private const MORPHS = [
        'images' => 'fileable_type',
        'comments' => 'commentable_type',
        'favorites' => 'favorited_type',
        'views' => 'viewable_type',
    ];

    public function up(): void
    {
        foreach (self::MORPHS as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->where($column, 'App\Models\Event')->delete();
            }
        }

        // Väzobná tabuľka ide prvá — odkazuje na `events`.
        Schema::dropIfExists('event_person');
        Schema::dropIfExists('event_subscribes');
        Schema::dropIfExists('events');
    }

    /**
     * Vráti štruktúru, nie obsah. Slúži len na to, aby sa dala migrácia
     * odrolovať v lokálnom prostredí bez pádu.
     */
    public function down(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->text('body_ai')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->unsignedInteger('village_id')->nullable();
            $table->dateTime('published')->nullable();
            $table->string('appendFile')->nullable();
            $table->integer('count_view')->default(0);
            $table->integer('ticket_available')->default(0);
            $table->integer('ticket_staff')->default(0);
            $table->unsignedInteger('organization_id');
            $table->string('street')->nullable();
            $table->string('registration');
            $table->string('entryFee');
            $table->boolean('disabled')->default(false);
            $table->string('clientwww')->nullable();
            $table->string('online_link')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('orginal_source', 255)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived', ''])->default('draft');
            $table->integer('venue_id')->nullable();
        });

        Schema::create('event_subscribes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id')->index();
            $table->unsignedInteger('organization_id')->index();
            $table->boolean('active')->default(true);
            $table->decimal('paid', 8, 2)->default(0);
            $table->dateTime('confirmed')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['organization_id', 'event_id']);
        });

        Schema::create('event_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id')->index();
            $table->unsignedInteger('event_id')->index();
            $table->timestamps();
        });
    }
};
