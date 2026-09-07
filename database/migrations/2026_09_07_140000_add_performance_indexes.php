<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabuľky `posts`, `prayers`, `comments` a `favorites` mali doteraz len primárny
 * kľúč, takže každý výpis na verejnej časti končil full scanom a filesortom nad
 * desiatkami tisíc riadkov. Indexy nižšie kopírujú reálne dopyty aplikácie —
 * najprv stĺpce porovnávané na rovnosť (vrátane `IS NULL`), až potom stĺpec,
 * podľa ktorého sa radí.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Migrácie zakladajúce tieto tabuľky v repozitári chýbajú (schéma vznikla
        // historicky), takže na čistej testovacej databáze tu ešte nie sú.
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            // Hlavný výpis: globálny scope youtube_blocked = 0, soft delete
            // a `video_available IS NULL` z postsByUpdater(), zoradené podľa
            // created_at. Index pokrýva aj count(*) pre stránkovanie.
            $table->index(
                ['youtube_blocked', 'video_available', 'deleted_at', 'created_at'],
                'posts_feed_created_index'
            );

            // Ten istý výpis zoradený filtrom `mostVisited`.
            $table->index(
                ['youtube_blocked', 'video_available', 'deleted_at', 'count_view'],
                'posts_feed_views_index'
            );

            // Profil kanála a "Ďalšie z kanála" na detaile príspevku — vrátane
            // count(*) pre stránkovanie, ktoré ináč muselo siahať na riadky.
            $table->index(
                ['organization_id', 'youtube_blocked', 'deleted_at', 'created_at'],
                'posts_organization_created_index'
            );

            // Počty príspevkov na kanál v bočnom paneli (frontOrganizationsList).
            $table->index(['youtube_blocked', 'organization_id'], 'posts_organization_count_index');

            // Buffer a admin filtre nad nezverejnenými príspevkami.
            $table->index('published', 'posts_published_index');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->index(['deleted_at', 'created_at'], 'prayers_created_index');
            $table->index(['deleted_at', 'fulfilled_at'], 'prayers_fulfilled_index');
            $table->index('organization_id', 'prayers_organization_id_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['deleted_at', 'created_at'], 'comments_created_index');
        });

        Schema::table('favorites', function (Blueprint $table) {
            // Morfologická väzba favorited() sa dopytuje na dvojicu typ + id.
            // Existujúci unique index začína user_id, takže sa preň nedal použiť.
            $table->index(['favorited_type', 'favorited_id'], 'favorites_favorited_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_feed_created_index');
            $table->dropIndex('posts_feed_views_index');
            $table->dropIndex('posts_organization_created_index');
            $table->dropIndex('posts_organization_count_index');
            $table->dropIndex('posts_published_index');
        });

        Schema::table('prayers', function (Blueprint $table) {
            $table->dropIndex('prayers_created_index');
            $table->dropIndex('prayers_fulfilled_index');
            $table->dropIndex('prayers_organization_id_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_created_index');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex('favorites_favorited_index');
        });
    }
};
