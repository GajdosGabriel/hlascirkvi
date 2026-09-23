<?php

namespace App\Console\Commands;

use App\Models\Canal;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\Seminar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Zjavné duplicity kanálov: rovnaký základ názvu (bez „(2)“, „(3)“…), ktorý
 * pod sebou spravuje ten istý užívateľ — napr. „Voľné“ a „Voľné (2)“ oba pod
 * jedným účtom. Menovci s rôznymi účtami (dve rôzne „Erika Banduričová“, tri
 * rôzne „Mária Mária“) sa nezlučujú: zhoda mena sama osebe nedokazuje, že ide
 * o toho istého človeka, a zlúčenie by mohlo spojiť obsah dvoch cudzích ľudí.
 *
 * Skupina s viac než dvoma kanálmi (napr. omylom trikrát) ponechá ako
 * „keeper“ živý kanál s najviac obsahom (a pri zhode najstarší), ostatné
 * zlúči doň a soft-deletne.
 */
class CanalsDedupe extends Command
{
    protected $signature = 'canals:dedupe {--fix : Zapísať zlúčenie (bez prepínača len vypíše návrh)}';

    protected $description = 'Nájde kanály s rovnakým názvom pod tým istým správcom a zlúči ich príspevky, modlitby, semináre, obľúbené, komentáre a správcov na jeden';

    public function handle(): int
    {
        $groups = $this->duplicateGroups();

        if ($groups === []) {
            $this->info('Žiadne zjavné duplicity nenájdené.');

            return self::SUCCESS;
        }

        $rows = [];
        $merged = 0;

        foreach ($groups as $group) {
            $keeper = $group['keeper'];

            foreach ($group['losers'] as $loser) {
                $rows[] = [
                    $loser->id, $loser->title, $loser->trashed() ? 'zmazaný' : 'živý',
                    $keeper->id, $keeper->title,
                ];

                if ($this->option('fix')) {
                    DB::transaction(fn () => $this->merge($loser, $keeper));
                }

                $merged++;
            }
        }

        $this->table(['duplikát', 'názov', 'stav', 'zostáva', 'názov'], $rows);
        $this->newLine();
        $this->info(($this->option('fix') ? 'Zlúčených' : 'Na zlúčenie') . ": {$merged}");

        if (! $this->option('fix')) {
            $this->warn('Nič sa nezapisovalo. Na zápis spustite príkaz s --fix.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{keeper: Canal, losers: array<int, Canal>}>
     */
    private function duplicateGroups(): array
    {
        $canals = Canal::withTrashed()
            ->withCount(['posts' => fn ($q) => $q->withTrashed(), 'prayers' => fn ($q) => $q->withTrashed(), 'seminars' => fn ($q) => $q->withTrashed()])
            ->get();

        $baseKey = fn (string $title) => Str::lower(Str::ascii(preg_replace('/\s*\(\d+\)$/u', '', trim($title))));

        $managersByCanal = DB::table('canal_user')
            ->select('canal_id', 'user_id')
            ->get()
            ->groupBy('canal_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->all());

        $groups = [];

        foreach ($canals->groupBy(fn (Canal $c) => $baseKey($c->title)) as $sameTitle) {
            if ($sameTitle->count() < 2) {
                continue;
            }

            // V rámci zhodného názvu zoskup podľa spoločného správcu — len to
            // je dôkaz duplicity, nie samotná zhoda mena.
            $byManager = [];
            foreach ($sameTitle as $canal) {
                foreach ($managersByCanal[$canal->id] ?? [] as $userId) {
                    $byManager[$userId][] = $canal;
                }
            }

            foreach ($byManager as $ownedCanals) {
                if (count($ownedCanals) < 2) {
                    continue;
                }

                $ordered = collect($ownedCanals)->sortBy([
                    fn (Canal $a, Canal $b) => $a->trashed() <=> $b->trashed(),
                    fn (Canal $a, Canal $b) => ($b->posts_count + $b->prayers_count + $b->seminars_count) <=> ($a->posts_count + $a->prayers_count + $a->seminars_count),
                    fn (Canal $a, Canal $b) => $a->id <=> $b->id,
                ])->values();

                // Bez živého kanála v skupine nie je čo na webe opraviť.
                if ($ordered->first()->trashed()) {
                    continue;
                }

                $groups[] = [
                    'keeper' => $ordered->first(),
                    'losers' => $ordered->slice(1)->all(),
                ];
            }
        }

        return $groups;
    }

    private function merge(Canal $loser, Canal $keeper): void
    {
        Post::withTrashed()->where('canal_id', $loser->id)->update(['canal_id' => $keeper->id]);
        Prayer::withTrashed()->where('canal_id', $loser->id)->update(['canal_id' => $keeper->id]);
        Seminar::withTrashed()->where('canal_id', $loser->id)->update(['canal_id' => $keeper->id]);

        DB::table('comments')
            ->where('commentable_type', Canal::class)
            ->where('commentable_id', $loser->id)
            ->update(['commentable_id' => $keeper->id]);

        DB::table('images')
            ->where('fileable_type', Canal::class)
            ->where('fileable_id', $loser->id)
            ->update(['fileable_id' => $keeper->id]);

        $this->mergeFavorites($loser, $keeper);
        $this->mergeManagers($loser, $keeper);
        $this->mergeAvatar($loser, $keeper);
        $this->backfillMetadata($loser, $keeper);

        if (! $loser->trashed()) {
            $loser->delete();
        }
    }

    /**
     * Presunie obľúbenie kanála, no ak ten istý užívateľ má obľúbený už aj
     * keeper, presun by narazil na unique(user_id, favorited_id, favorited_type)
     * — vtedy sa duplicitný riadok len zahodí.
     */
    private function mergeFavorites(Canal $loser, Canal $keeper): void
    {
        $existing = Favorite::where('favorited_type', Canal::class)
            ->where('favorited_id', $keeper->id)
            ->pluck('user_id')
            ->all();

        Favorite::where('favorited_type', Canal::class)
            ->where('favorited_id', $loser->id)
            ->whereIn('user_id', $existing)
            ->delete();

        Favorite::where('favorited_type', Canal::class)
            ->where('favorited_id', $loser->id)
            ->update(['favorited_id' => $keeper->id]);
    }

    private function mergeManagers(Canal $loser, Canal $keeper): void
    {
        $keeper->users()->syncWithoutDetaching($loser->users()->pluck('users.id')->all());

        DB::table('canal_user')->where('canal_id', $loser->id)->delete();
    }

    /**
     * Avatar je len meno súboru, obrázok sa číta z disku podľa ID kanála
     * (organizations/{id}/…) — kopírovať sa dá len fyzickým presunom súboru.
     */
    private function mergeAvatar(Canal $loser, Canal $keeper): void
    {
        if (blank($loser->avatar) || filled($keeper->avatar)) {
            return;
        }

        $from = 'organizations/'.$loser->id.'/'.$loser->avatar;
        $to = 'organizations/'.$keeper->id.'/'.$loser->avatar;

        if (! Storage::disk('public')->exists($from)) {
            return;
        }

        Storage::disk('public')->move($from, $to);
        $keeper->update(['avatar' => $loser->avatar]);
    }

    /**
     * Doplní na keeperovi len prázdne polia — obsah, ktorý keeper už má,
     * duplikát neprepisuje.
     */
    private function backfillMetadata(Canal $loser, Canal $keeper): void
    {
        $fields = [
            'description', 'email', 'phone', 'street', 'psc', 'mod_title',
            'denomination', 'kind', 'youtube_channel', 'youtube_playlist', 'url_www',
        ];

        $fill = [];
        foreach ($fields as $field) {
            if (blank($keeper->{$field}) && filled($loser->{$field})) {
                $fill[$field] = $loser->{$field};
            }
        }

        if ($fill !== []) {
            $keeper->update($fill);
        }
    }
}
