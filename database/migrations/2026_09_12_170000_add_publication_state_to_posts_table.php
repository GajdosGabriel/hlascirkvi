<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stav zverejnenia príspevku ako stĺpce, nie ako riadok v `post_updater`.
 *
 * Doteraz platilo: príspevok je zverejnený práve vtedy, keď má aspoň jeden
 * updater, a ten istý updater určoval aj to, do ktorého výpisu patrí
 * (15 úvodná stránka, 16 nedeľné prenosy, 17 konferencie). Stĺpec `published`
 * pritom vypĺňal import každému príspevku hneď pri stiahnutí, takže sa podľa
 * neho nedalo rozlíšiť zverejnené od čakajúceho vo fronte — a aplikácia obe
 * definície používala naraz: buffer `doesntHave('updaters')`, nástenka
 * a filtre `whereNull('published')`. V databáze to je 426 príspevkov, ktoré
 * podľa jednej definície vyšli a podľa druhej čakajú.
 *
 * `published_at` je odteraz jediný stav (prázdne = čaká v bufferi),
 * `section` jediné zaradenie.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts') || Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('published');
            $table->string('section', 20)->default('front')->after('published_at');

            // Výpisy sa pýtajú „zverejnené v tejto sekcii, najnovšie hore"
            // a buffer „čo ešte nevyšlo" — oboje prejde týmto indexom.
            $table->index(['section', 'published_at'], 'posts_section_published_index');
        });

        $this->backfill();
    }

    /**
     * Prevod z `post_updater`. Čas vydania berieme zo stĺpca `published`;
     * kde chýba, zastúpi ho `created_at` — publisher pri zverejnení prepisuje
     * oba naraz (App\Repositories\Eloquent\EloquentPostRepository::publishPost).
     */
    protected function backfill(): void
    {
        if (! Schema::hasTable('post_updater') || ! Schema::hasTable('updaters')) {
            return;
        }

        $sections = [
            'front-post'  => 'front',
            'online-post' => 'live',
            'seminare-post' => 'seminar',
        ];

        foreach ($sections as $slug => $section) {
            $updaterId = DB::table('updaters')->where('slug', $slug)->value('id');

            if (! $updaterId) {
                continue;
            }

            DB::table('posts')
                ->join('post_updater', 'posts.id', '=', 'post_updater.post_id')
                ->where('post_updater.updater_id', $updaterId)
                ->update([
                    'posts.section' => $section,
                    'posts.published_at' => DB::raw('coalesce(posts.published, posts.created_at)'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_section_published_index');
            $table->dropColumn(['published_at', 'section']);
        });
    }
};
