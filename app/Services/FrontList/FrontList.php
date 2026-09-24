<?php

namespace App\Services\FrontList;

use App\Enums\CanalType;
use App\Models\Canal;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Predný zoznam kanálov — karty „Kresťanské osobnosti" a „Cirkvi
 * a spoločenstvá" v bočnom paneli.
 *
 * Kto v zozname je, určuje správca (`front_listed_at`), kam patrí, typ kanála
 * (`type`). Poradie na karte neurčuje nikto ručne:
 *
 *  1. Rebríček záujmu — zhliadnutia zverejnených príspevkov kanála a noví
 *     sledovatelia za posledné týždne, pričom starší záujem postupne stráca
 *     váhu (config frontlist.half_life_days). Navrchu je teda ten, koho ľudia
 *     pozerajú teraz, nie ten, kto má najviac videí za desať rokov.
 *  2. Objavovacie miesta — pár miest na karte dostanú kanály, ktoré práve
 *     niečo vydali, no do rebríčka sa nedostali. Striedajú sa každý deň.
 *
 * Celý zoznam na /osobnosti je abecedný — tam sa hľadá konkrétne meno.
 */
class FrontList
{
    public const CACHE_KEY = 'frontlist:canals:v2';

    /**
     * Karta do bočného panela. Stojí na každej stránke s panelom, preto
     * z cache; výber objavovacích miest sa robí až po nej, aby sa striedal
     * o polnoci a nie s cache.
     *
     * @return Collection<int, FrontListItem>
     */
    public function forCard(CanalType $type): Collection
    {
        return $this->pick($this->cached(), $type);
    }

    /**
     * Celý zoznam jedného typu pre verejnú stránku, abecedne.
     *
     * @return Collection<int, FrontListItem>
     */
    public function all(CanalType $type): Collection
    {
        return $this->cached()->filter(fn (FrontListItem $item) => $item->type === $type)->values();
    }

    public function total(CanalType $type): int
    {
        return $this->all($type)->count();
    }

    /**
     * Zoznam pre správcu — vždy čerstvý, zoradený podľa typu a záujmu.
     * Obsahuje aj kanály bez typu, ktoré na webe nevidno.
     *
     * @return Collection<int, FrontListItem>
     */
    public function forAdmin(): Collection
    {
        return $this->fresh()
            ->sortBy([
                fn (FrontListItem $a, FrontListItem $b) => ($a->type?->value ?? 'z') <=> ($b->type?->value ?? 'z'),
                fn (FrontListItem $a, FrontListItem $b) => $b->score <=> $a->score,
            ])
            ->values();
    }

    /**
     * Id kanálov, ktoré sú práve na kartách — pre štítok v admine.
     *
     * @param  Collection<int, FrontListItem>  $items
     * @return array<int, int>
     */
    public function cardIds(Collection $items): array
    {
        return collect(CanalType::cases())
            ->flatMap(fn (CanalType $type) => $this->pick($items, $type)->pluck('id'))
            ->all();
    }

    public function add(Canal $canal, ?CanalType $type = null): void
    {
        $canal->front_listed_at ??= now();
        $canal->type = $type ?? $canal->type;
        $canal->save();

        $this->forget();
    }

    public function remove(Canal $canal): void
    {
        $canal->fill(['front_listed_at' => null])->save();

        $this->forget();
    }

    public function setType(Canal $canal, CanalType $type): void
    {
        $canal->fill(['type' => $type])->save();

        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Výber na kartu: najprv rebríček záujmu, potom objavovacie miesta.
     * Keď na ne nie je dosť kandidátov, doplní sa rebríček — karta sa tak
     * na novej inštalácii bez zhliadnutí naplní abecedne.
     *
     * @param  Collection<int, FrontListItem>  $items
     * @return Collection<int, FrontListItem>
     */
    protected function pick(Collection $items, CanalType $type): Collection
    {
        $limit = max(0, (int) config('frontlist.card_limit'));
        $slots = min($limit, max(0, (int) config('frontlist.discovery_slots')));

        // sortBy je stabilné, takže pri rovnakom skóre ostáva abeceda.
        $ranked = $items
            ->filter(fn (FrontListItem $item) => $item->type === $type)
            ->sortByDesc(fn (FrontListItem $item) => $item->score)
            ->values();

        $top = $ranked->filter(fn (FrontListItem $item) => $item->score > 0)->take($limit - $slots);

        $discovery = $this->rotate(
            $ranked->whereNotIn('id', $top->pluck('id'))->filter->hasFreshPost()
        )->take($slots);

        $chosen = $top->concat($discovery);

        return $chosen
            ->concat($ranked->whereNotIn('id', $chosen->pluck('id'))->take($limit - $chosen->count()))
            ->values();
    }

    /**
     * Poradie, ktoré sa mení každý deň, ale v rámci dňa drží — karta
     * nepreskakuje pri každom načítaní stránky.
     *
     * @param  Collection<int, FrontListItem>  $items
     * @return Collection<int, FrontListItem>
     */
    protected function rotate(Collection $items): Collection
    {
        $den = now()->toDateString();

        return $items->sortBy(fn (FrontListItem $item) => crc32($den . ':' . $item->id))->values();
    }

    /**
     * @return Collection<int, FrontListItem>
     */
    protected function cached(): Collection
    {
        // Do cache ide holé pole, nie modely: config/cache.php má
        // `serializable_classes` na false, takže objekt by sa odtiaľ vrátil
        // ako __PHP_Incomplete_Class.
        $rows = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes((int) config('frontlist.cache_minutes')),
            fn () => $this->fresh()->map->toArray()->all()
        );

        return collect($rows)->map(FrontListItem::fromArray(...));
    }

    /**
     * @return Collection<int, FrontListItem>
     */
    protected function fresh(): Collection
    {
        $canals = Canal::onFrontList()
            // Kanál si inak ku každému riadku dotiahne obľúbené, hoci zoznam
            // z neho potrebuje meno, obrázok a dve čísla.
            ->without('favorites')
            ->select(['id', 'title', 'slug', 'avatar', 'type', 'front_listed_at'])
            // Zverejnené príspevky, nie všetky: pôvodný dopyt ukazoval pri
            // ECAV 3190 a pri Slovenskom dohovore 6348, čo boli počty
            // vrátane toho, čo ešte čaká v bufferi.
            ->withCount(['posts as posts_count' => fn ($query) => $query->published()])
            ->withMax(['posts as last_post_at' => fn ($query) => $query->published()], 'created_at')
            ->get();

        $scores = $this->scores($canals->modelKeys());

        return $canals->map(fn (Canal $canal) => FrontListItem::fromCanal($canal, $scores[$canal->id] ?? 0.0));
    }

    /**
     * Záujem o kanály za posledných `window_days` dní. Každé zhliadnutie
     * príspevku aj každý nový sledovateľ má váhu 0,5^(vek v dňoch / polčas).
     *
     * Dva grupované dopyty nad celým zoznamom naraz, nie dopyt na kanál.
     * Zhliadnutia sa čítajú z `views`, kde je jeden návštevník najviac raz
     * za deň na príspevok (ViewRecorder) — preklikávanie čísla nenafúkne.
     *
     * @param  array<int, int>  $ids
     * @return array<int, float>
     */
    protected function scores(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $today    = now()->toDateString();
        $window   = max(1, (int) config('frontlist.window_days'));
        $halfLife = max(1, (int) config('frontlist.half_life_days'));
        $from     = now()->subDays($window)->startOfDay();

        $views = DB::table('views')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->where('views.viewable_type', (new Post)->getMorphClass())
            ->where('views.viewed_on', '>=', $from->toDateString())
            ->whereIn('posts.canal_id', $ids)
            ->whereNotNull('posts.published_at')
            ->whereNull('posts.deleted_at')
            ->groupBy('posts.canal_id')
            ->selectRaw('posts.canal_id as canal_id, sum(pow(0.5, datediff(?, views.viewed_on) / ?)) as score', [$today, $halfLife])
            ->pluck('score', 'canal_id');

        $follows = DB::table('favorites')
            ->where('favorited_type', (new Canal)->getMorphClass())
            ->whereIn('favorited_id', $ids)
            ->where('created_at', '>=', $from)
            ->groupBy('favorited_id')
            ->selectRaw('favorited_id as canal_id, sum(pow(0.5, datediff(?, created_at) / ?)) as score', [$today, $halfLife])
            ->pluck('score', 'canal_id');

        $followWeight = (float) config('frontlist.follow_weight');
        $scores = [];

        foreach ($ids as $id) {
            $score = (float) ($views[$id] ?? 0) + $followWeight * (float) ($follows[$id] ?? 0);

            if ($score > 0) {
                $scores[$id] = round($score, 2);
            }
        }

        return $scores;
    }
}
