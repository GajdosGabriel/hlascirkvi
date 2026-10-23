<?php

use App\Models\Comment;
use App\Models\Prayer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Upratanie anomálií nájdených v produkčnej databáze (10/2026):
 *
 * - Modlitby odoslané viackrát (dvojklik, opakovaný import kanála 100 v 6/2022)
 *   — rovnaký kanál, meno a text. Ostáva najstaršia, ~340 kópií sa skryje.
 * - Spam s ponukou pôžičiek medzi modlitbami („DAVID LISKA“, „OKFin“ …).
 * - Komentáre importované z YouTube dvakrát — staré importy ešte nemali
 *   youtube_comment_id, a tak ich ďalší import nespoznal. Rovnaký príspevok,
 *   autor, rodič a text. Ostáva kópia s youtube_comment_id (podľa nej beží
 *   synchronizácia), inak najstaršia; odpovede a obľúbené sa presmerujú na ňu.
 * - model_has_roles po natvrdo zmazaných užívateľoch a 21 starých účtov
 *   bez roly „user“, ktorú inak dostane každý nový účet (UserObserver).
 * - Zamyslenia bez názvu a slugu (odkazy „predošlé/ďalšie“ viedli na
 *   /zamyslenia/ = dnešné) a dve s rovnakým slugom (druhé bolo nedostupné).
 *
 * Duplicity sa skrývajú mäkkým zmazaním s pevným časom, podľa ktorého down()
 * obnoví presne tieto záznamy. Presmerované odpovede a obľúbené ostávajú
 * na zachovanej kópii.
 */
return new class extends Migration
{
    private const HIDDEN_AT = '2026-10-23 10:00:00';

    public function up(): void
    {
        DB::statement('SET SESSION group_concat_max_len = 1000000');

        DB::transaction(function () {
            $this->dedupePrayers();
            $this->hideLoanSpam();
            $this->dedupeComments();
            $this->fixRoles();
            $this->fixVerseSlugs();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach (['prayers', 'comments'] as $table) {
            DB::table($table)->where('deleted_at', self::HIDDEN_AT)->update(['deleted_at' => null]);
        }
    }

    private function dedupePrayers(): void
    {
        // BINARY: kolácia _ci by zlúčila aj texty líšiace sa len diakritikou.
        $groups = DB::table('prayers')
            ->whereNull('deleted_at')
            ->selectRaw('MIN(id) AS keep_id, GROUP_CONCAT(id) AS ids')
            ->groupByRaw('canal_id, user_name, BINARY body')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $this->hide('prayers', Prayer::class, (int) $group->keep_id, $group->ids);
        }
    }

    private function hideLoanSpam(): void
    {
        DB::table('prayers')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where(fn ($q) => $q->where('user_name', 'DAVID LISKA')->where('body', 'like', 'Mám kapitál, ktorý sa používa na poskytovanie%'))
                    ->orWhere(fn ($q) => $q->where('user_name', 'OKFin')->where('body', 'like', 'Môžete získať až 10 miliónov eur.%'))
                    ->orWhere(fn ($q) => $q->where('user_name', 'roller')->where('body', 'like', 'Ahoj, Potřebujete osobní půjčku%'))
                    ->orWhere(fn ($q) => $q->where('user_name', 'Siegfried')->where('body', 'like', 'Ponúkame všetky druhy pôžičiek%'));
            })
            ->update(['deleted_at' => self::HIDDEN_AT]);
    }

    private function dedupeComments(): void
    {
        // Po presmerovaní odpovedí na zachovaného rodiča sa z odpovedí môžu
        // stať nové duplicity, preto sa kolo opakuje, kým nejaké nájde.
        for ($round = 0; $round < 5; $round++) {
            $groups = DB::table('comments')
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(MIN(CASE WHEN youtube_comment_id IS NOT NULL THEN id END), MIN(id)) AS keep_id, GROUP_CONCAT(id) AS ids')
                ->groupByRaw('commentable_type, commentable_id, user_id, user_name, parent_id, BINARY body')
                ->havingRaw('COUNT(*) > 1')
                // Dva rôzne youtube_comment_id = autor naozaj napísal to isté dvakrát.
                ->havingRaw('COUNT(youtube_comment_id) <= 1')
                ->get();

            if ($groups->isEmpty()) {
                return;
            }

            // reply_to_id nemá index — namiesto dopytu pri každej skupine sa
            // odkazované ID načítajú raz a presmeruje sa len tam, kde treba.
            $referenced = DB::table('comments')->whereNotNull('parent_id')->distinct()->pluck('parent_id')
                ->merge(DB::table('comments')->whereNotNull('reply_to_id')->distinct()->pluck('reply_to_id'))
                ->flip();

            foreach ($groups as $group) {
                $keepId = (int) $group->keep_id;
                $dupIds = array_filter($this->otherIds($group->ids, $keepId), fn ($id) => $referenced->has($id));

                if ($dupIds) {
                    DB::table('comments')->whereIn('parent_id', $dupIds)->update(['parent_id' => $keepId]);
                    DB::table('comments')->whereIn('reply_to_id', $dupIds)->update(['reply_to_id' => $keepId]);
                }

                $this->hide('comments', Comment::class, $keepId, $group->ids);
            }
        }
    }

    private function hide(string $table, string $type, int $keepId, string $ids): void
    {
        $dupIds = $this->otherIds($ids, $keepId);

        // Obľúbené prejdú na zachovanú kópiu; kto ju už má, tomu sa zmaže tá navyše.
        $favorites = DB::table('favorites')->where('favorited_type', $type)->whereIn('favorited_id', $dupIds)->get();

        foreach ($favorites as $favorite) {
            $exists = DB::table('favorites')
                ->where('favorited_type', $type)
                ->where('favorited_id', $keepId)
                ->where(fn ($q) => $favorite->user_id !== null
                    ? $q->where('user_id', $favorite->user_id)
                    : $q->where('pending_user_id', $favorite->pending_user_id))
                ->exists();

            $exists
                ? DB::table('favorites')->where('id', $favorite->id)->delete()
                : DB::table('favorites')->where('id', $favorite->id)->update(['favorited_id' => $keepId]);
        }

        DB::table($table)->whereIn('id', $dupIds)->whereNull('deleted_at')->update(['deleted_at' => self::HIDDEN_AT]);
    }

    private function otherIds(string $ids, int $keepId): array
    {
        return array_values(array_diff(array_map('intval', explode(',', $ids)), [$keepId]));
    }

    private function fixRoles(): void
    {
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereNotIn('model_id', DB::table('users')->select('id'))
            ->delete();

        $roleId = DB::table('roles')->where('name', 'user')->where('guard_name', 'web')->value('id');

        if ($roleId === null) {
            return;
        }

        $userIds = DB::table('users')
            ->whereNotIn('id', DB::table('model_has_roles')->where('model_type', User::class)->select('model_id'))
            ->pluck('id');

        DB::table('model_has_roles')->insert($userIds->map(fn ($id) => [
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $id,
        ])->all());
    }

    /**
     * Názov chýbajúcemu zamysleniu dá text, ku ktorému je napísané
     * (takto sú pomenované aj niektoré ďalšie, napr. „Filemonovi 1-25“).
     */
    private function fixVerseSlugs(): void
    {
        $taken = [];

        foreach (DB::table('verses')->orderBy('id')->get(['id', 'title', 'slug', 'text_na_zamyslenie']) as $verse) {
            $title = $verse->title;
            $slug = (string) $verse->slug;

            if ($slug === '') {
                $title = $title ?: $verse->text_na_zamyslenie;
                $slug = Str::slug((string) $title);
            }

            $base = $slug;
            for ($i = 2; $slug === '' || isset($taken[$slug]); $i++) {
                $slug = ($base === '' ? 'zamyslenie-'.$verse->id : $base).'-'.$i;
            }
            $taken[$slug] = true;

            if ($slug !== $verse->slug || $title !== $verse->title) {
                DB::table('verses')->where('id', $verse->id)->update(['title' => $title, 'slug' => $slug]);
            }
        }
    }
};
