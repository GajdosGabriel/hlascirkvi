<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Vyčistenie názvov kanálov (9/2026). Duplicity vznikali hlavne tak, že
 * UserObserver zakladal menovcom osobný kanál s rovnakým názvom a pravidlo
 * unique platilo len vo formulári. Collation porovnáva bez diakritiky
 * a veľkosti písmen, takže „Mária Mária" = „Maria Maria", „Voľné" = „voľné".
 *
 * 1. Prázdny duplikát — bez správcu, príspevkov, modlitieb, seminárov aj
 *    YouTube zdroja — sa skryje (soft delete).
 * 2. Z názvu zmiznú emoji; názov kratší ako 2 znaky dostane meno správcu.
 * 3. Zo skupiny rovnakých názvov si ho ponechá živý kanál s najviac
 *    príspevkami (potom najstarší), ostatné dostanú poradové číslo „(2)".
 *
 * Zmeny sú jednorazové úpravy dát, down() ich nevracia.
 */
return new class extends Migration
{
    private const EMOJI = '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{1F1E6}-\x{1F1FF}\x{FE0F}\x{200D}]/u';

    public function up(): void
    {
        $canals = DB::table('canals')
            ->leftJoinSub(DB::table('posts')->selectRaw('canal_id, count(*) n')->groupBy('canal_id'), 'p', 'p.canal_id', '=', 'canals.id')
            ->select('canals.id', 'canals.title', 'canals.deleted_at', 'canals.youtube_channel', 'canals.youtube_playlist', DB::raw('coalesce(p.n, 0) as posts'))
            ->get()
            ->keyBy('id');

        $key = fn (string $title) => Str::lower(Str::ascii(trim($title)));
        $live = $canals->whereNull('deleted_at');
        $counts = $live->countBy(fn ($c) => $key($c->title))->all();

        // 1. Prázdne duplikáty
        foreach ($live as $canal) {
            $empty = $canal->posts == 0
                && blank($canal->youtube_channel) && blank($canal->youtube_playlist)
                && ! DB::table('canal_user')->where('canal_id', $canal->id)->exists()
                && ! DB::table('prayers')->where('canal_id', $canal->id)->exists()
                && ! DB::table('seminars')->where('canal_id', $canal->id)->exists();

            if ($empty && $counts[$key($canal->title)] > 1) {
                DB::table('canals')->where('id', $canal->id)->update(['deleted_at' => now()]);
                $canal->deleted_at = now();
                $counts[$key($canal->title)]--;
            }
        }

        // 2. Emoji a príliš krátke názvy
        foreach ($canals as $canal) {
            $title = trim(preg_replace('/\s+/u', ' ', preg_replace(self::EMOJI, '', $canal->title)));

            if (mb_strlen($title) < 2) {
                $owner = DB::table('canal_user')->join('users', 'users.id', '=', 'canal_user.user_id')
                    ->where('canal_user.canal_id', $canal->id)->orderBy('users.id')
                    ->first(['users.first_name', 'users.last_name']);
                $title = $owner ? trim($owner->first_name . ' ' . $owner->last_name) : '';
                $title = mb_strlen($title) >= 2 ? $title : 'Kanál';
            }

            $canal->title = $title;
        }

        // 3. Duplicity: poradie určuje, kto si názov ponechá
        $ordered = $canals->sortBy([
            fn ($a, $b) => ($a->deleted_at !== null) <=> ($b->deleted_at !== null),
            fn ($a, $b) => $b->posts <=> $a->posts,
            fn ($a, $b) => $a->id <=> $b->id,
        ]);

        $taken = [];
        foreach ($ordered as $canal) {
            $title = $canal->title;
            for ($n = 2; isset($taken[$key($title)]); $n++) {
                $title = $canal->title . ' (' . $n . ')';
            }
            $taken[$key($title)] = true;

            if ($title !== DB::table('canals')->where('id', $canal->id)->value('title')) {
                DB::table('canals')->where('id', $canal->id)->update(['title' => $title]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
