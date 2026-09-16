<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Presun kanálov z `organizations` do `canals`.
 *
 * Model sa v 9/2026 premenoval z Organization na Canal, databáza však ostala
 * pri starom mene — App\Models\Canal musel mať `protected $table` aj vlastný
 * getForeignKey(), každý surový dopyt písal `organizations` a polymorfné
 * stĺpce držali triedu `App\Models\Organization` cez morph mapu. Táto migrácia
 * to dorovnáva:
 *
 *   organizations            -> canals
 *   organization_user        -> canal_user (organization_id -> canal_id)
 *   posts.organization_id    -> posts.canal_id
 *   prayers.organization_id  -> prayers.canal_id
 *   seminars.organization_id -> seminars.canal_id
 *   buffer_publications.organization_id -> buffer_publications.canal_id
 *   users.org_id             -> users.canal_id
 *   favorites.favorited_type -> App\Models\Canal
 *
 * Tabuľka `organizations` po migrácii ostáva — prázdna, len ako štruktúra.
 *
 * Verejné adresy /organizations/{id}, /api/organization/{id} a
 * /organizations/{id}/message ostávajú nedotknuté — sú rozposlané v e-mailoch
 * a zaindexované vo vyhľadávačoch. Menia sa len tabuľky a stĺpce pod nimi.
 *
 * Dve veci, ktoré tu vyzerajú ako zbytočná repetícia, ale nie sú:
 *
 * 1. Indexy sa nepremenúvajú cez renameIndex(). To sa kompiluje na
 *    `ALTER TABLE ... RENAME INDEX`, ktoré MariaDB vie až od 10.5.2 a vývojové
 *    aj testovacie prostredie beží na 10.4. Preto drop + create.
 *
 * 2. Celý rollback je natvrdo v down(), nie v pomocných metódach. Larastan si
 *    z migrácií skladá schému modelov a preskakuje pri tom jedinú metódu —
 *    tú s menom `down`. Čokoľvek v pomocnej metóde by si prečítal ako súčasť
 *    up() a premenovanie by si vzápätí zrušil, takže by `$post->canal_id`
 *    hlásil ako neexistujúci stĺpec.
 */
return new class extends Migration
{
    /**
     * Polymorfné stĺpce nesú aj mená spred presunu modelov do App\Models
     * (`App\Organization`, `App\Prayer`). Tie nemala pokryté ani morph mapa,
     * takže tie riadky boli dosiaľ mŕtve — favorited() im vracal null.
     */
    private const FAVORITE_TYPES = [
        'App\Models\Organization' => 'App\Models\Canal',
        'App\Organization' => 'App\Models\Canal',
        'App\Prayer' => 'App\Models\Prayer',
    ];

    public function up(): void
    {
        if (Schema::hasTable('canals') || ! Schema::hasTable('organizations')) {
            return;
        }

        // RENAME TABLE v InnoDB prepíše aj cudzie kľúče, ktoré na tabuľku
        // ukazujú (organization_user), takže ich netreba rušiť dopredu.
        Schema::rename('organizations', 'canals');

        // Prázdna škrupina pôvodnej tabuľky. Vytvára sa ešte pred premenovaním
        // indexov, aby si so sebou zobrala staré mená a nie tie kanálové.
        DB::statement('create table `organizations` like `canals`');

        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex('organizations_import_day_index');
            $table->dropIndex('organizations_front_list_index');

            $table->index('import_day', 'canals_import_day_index');
            $table->index(['front_listed_at', 'kind'], 'canals_front_list_index');
        });

        $this->renamePivot();
        $this->renamePosts();
        $this->renamePrayers();
        $this->renameSeminars();
        $this->renameBufferPublications();
        $this->renameUsers();

        foreach (self::FAVORITE_TYPES as $from => $to) {
            DB::table('favorites')->where('favorited_type', $from)->update(['favorited_type' => $to]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('canals')) {
            return;
        }

        // Späť sa dá vrátiť len jedno meno — ktorý riadok mal pôvodne
        // `App\Organization` a ktorý `App\Models\Organization`, už z dát
        // nezistíme. Pre aplikáciu je to jedno, morph mapa oboje mapovala
        // na ten istý model. `App\Prayer` sa nevracia vôbec, to bola chyba.
        DB::table('favorites')
            ->where('favorited_type', 'App\Models\Canal')
            ->update(['favorited_type' => 'App\Models\Organization']);

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'org_id');
        });

        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->dropIndex('buffer_publications_canal_id_index');
        });

        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'organization_id');
        });

        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->index('organization_id', 'buffer_publications_organization_id_index');
        });

        Schema::table('seminars', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'organization_id');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->dropIndex('prayers_canal_id_index');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'organization_id');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->index('organization_id', 'prayers_organization_id_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_canal_created_index');
            $table->dropIndex('posts_canal_count_index');
            $table->dropIndex('posts_canal_views_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'organization_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(
                ['organization_id', 'youtube_blocked', 'deleted_at', 'created_at'],
                'posts_organization_created_index'
            );
            $table->index(['youtube_blocked', 'organization_id'], 'posts_organization_count_index');
            $table->index(
                ['organization_id', 'youtube_blocked', 'deleted_at', 'count_view'],
                'posts_organization_views_index'
            );
        });

        // Tabuľka musí byť späť skôr, než sa pivotu zakladá cudzí kľúč na
        // `organizations` — do prázdnej škrupiny by riadky neprešli.
        Schema::dropIfExists('organizations');
        Schema::rename('canals', 'organizations');

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('canals_import_day_index');
            $table->dropIndex('canals_front_list_index');

            $table->index('import_day', 'organizations_import_day_index');
            $table->index(['front_listed_at', 'kind'], 'organizations_front_list_index');
        });

        Schema::table('canal_user', function (Blueprint $table) {
            $table->dropForeign('canal_user_canal_id_foreign');
            $table->dropForeign('canal_user_user_id_foreign');
            $table->dropIndex('canal_user_canal_id_index');
            $table->dropIndex('canal_user_user_id_index');
        });

        Schema::table('canal_user', function (Blueprint $table) {
            $table->renameColumn('canal_id', 'organization_id');
        });

        Schema::rename('canal_user', 'organization_user');

        Schema::table('organization_user', function (Blueprint $table) {
            $table->index('organization_id', 'organization_user_organization_id_index');
            $table->index('user_id', 'organization_user_user_id_index');

            $table->foreign('organization_id', 'organization_user_organization_id_foreign')
                ->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('user_id', 'organization_user_user_id_foreign')
                ->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Pivot užívateľ ↔ kanál. Cudzie kľúče sa rušia a zakladajú znova, aby
     * nezostali s menom `organization_user_*` nad tabuľkou `canal_user`.
     */
    private function renamePivot(): void
    {
        Schema::rename('organization_user', 'canal_user');

        Schema::table('canal_user', function (Blueprint $table) {
            $table->dropForeign('organization_user_organization_id_foreign');
            $table->dropForeign('organization_user_user_id_foreign');
            $table->dropIndex('organization_user_organization_id_index');
            $table->dropIndex('organization_user_user_id_index');
        });

        Schema::table('canal_user', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'canal_id');
        });

        Schema::table('canal_user', function (Blueprint $table) {
            $table->index('canal_id', 'canal_user_canal_id_index');
            $table->index('user_id', 'canal_user_user_id_index');

            $table->foreign('canal_id', 'canal_user_canal_id_foreign')
                ->references('id')->on('canals')->cascadeOnDelete();
            $table->foreign('user_id', 'canal_user_user_id_foreign')
                ->references('id')->on('users')->cascadeOnDelete();
        });
    }

    private function renamePosts(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_organization_created_index');
            $table->dropIndex('posts_organization_count_index');
            $table->dropIndex('posts_organization_views_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'canal_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(
                ['canal_id', 'youtube_blocked', 'deleted_at', 'created_at'],
                'posts_canal_created_index'
            );
            $table->index(['youtube_blocked', 'canal_id'], 'posts_canal_count_index');
            $table->index(
                ['canal_id', 'youtube_blocked', 'deleted_at', 'count_view'],
                'posts_canal_views_index'
            );
        });
    }

    private function renamePrayers(): void
    {
        Schema::table('prayers', function (Blueprint $table) {
            $table->dropIndex('prayers_organization_id_index');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'canal_id');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->index('canal_id', 'prayers_canal_id_index');
        });
    }

    private function renameSeminars(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'canal_id');
        });
    }

    private function renameBufferPublications(): void
    {
        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->dropIndex('buffer_publications_organization_id_index');
        });

        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'canal_id');
        });

        Schema::table('buffer_publications', function (Blueprint $table) {
            $table->index('canal_id', 'buffer_publications_canal_id_index');
        });
    }

    /**
     * `users.org_id` je aktívny kanál, do ktorého užívateľ píše. Skratka
     * ostala z čias, keď sa kanál volal organizácia.
     */
    private function renameUsers(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('org_id', 'canal_id');
        });
    }
};
