<?php

namespace App\Services\FrontList;

use App\Models\Canal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Predný zoznam kanálov — karta „Kresťanské osobnosti" na úvodnej stránke.
 *
 * Zaradenie a poradie nesie samotný kanál (`front_listed_at`,
 * `front_position`). Do 9/2026 to bol riadok v `organization_updater`
 * s updaterom 14 a dopyt obchádzal model: nekontroloval zmazanie ani skrytie
 * kanála, počty príspevkov rátal aj z nezverejnených a kanál bez jediného
 * príspevku z karty vypadol (INNER JOIN). Všetko toto tu robí Eloquent.
 */
class FrontList
{
    public const CACHE_KEY = 'frontlist:canals';

    /**
     * Zoznam pre bočný panel. Karta stojí na každej stránke s bočným
     * panelom, preto z cache — mení sa ručne, počty príspevkov raz denne.
     *
     * @return Collection<int, FrontListItem>
     */
    public function forCard(): Collection
    {
        return $this->cached()->take((int) config('frontlist.card_limit'))->values();
    }

    /**
     * Celý zoznam pre verejnú stránku za odkazom „zobraziť všetky".
     *
     * @return Collection<int, FrontListItem>
     */
    public function all(): Collection
    {
        return $this->cached();
    }

    public function total(): int
    {
        return $this->cached()->count();
    }

    /**
     * Zoznam pre správcu — vždy čerstvý, aby po zmene poradia nepozeral
     * na cache.
     *
     * @return Collection<int, FrontListItem>
     */
    public function forAdmin(): Collection
    {
        return $this->query()->get()->map(FrontListItem::fromCanal(...));
    }

    /**
     * Kanál do zoznamu. Poradie dostane až za posledným — kam presne patrí,
     * si správca posunie sám.
     */
    public function add(Canal $canal): void
    {
        if ($canal->front_listed_at) {
            return;
        }

        $canal->fill([
            'front_listed_at' => now(),
            'front_position'  => (int) Canal::whereNotNull('front_listed_at')->max('front_position') + 10,
        ])->save();

        $this->forget();
    }

    public function remove(Canal $canal): void
    {
        $canal->fill([
            'front_listed_at' => null,
            'front_position'  => null,
        ])->save();

        $this->forget();
    }

    /**
     * Posun kanála o jedno miesto hore ($smer = -1) alebo dole (+1).
     */
    public function move(Canal $canal, int $smer): void
    {
        if (! $canal->front_listed_at) {
            return;
        }

        $poradie = $this->forAdmin()->pluck('id')->all();
        $index   = array_search((int) $canal->id, $poradie, true);

        if ($index === false || ! isset($poradie[$index + $smer])) {
            return;
        }

        [$poradie[$index], $poradie[$index + $smer]] = [$poradie[$index + $smer], $poradie[$index]];

        $this->reorder($poradie);
    }

    /**
     * Nové poradie zoznamu podľa poľa id. Prečísluje sa celý zoznam
     * desiatkami — je krátky a takto sa z neho nikdy nestane rad rovnakých
     * čísel, v ktorom sa už posúvať nedá.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $index => $id) {
            Canal::whereKey($id)
                ->whereNotNull('front_listed_at')
                ->update(['front_position' => ($index + 1) * 10]);
        }

        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
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
            fn () => $this->forAdmin()->map->toArray()->all()
        );

        return collect($rows)->map(FrontListItem::fromArray(...));
    }

    protected function query()
    {
        return Canal::onFrontList()
            // Kanál si inak ku každému riadku dotiahne obľúbené, hoci zoznam
            // z neho potrebuje meno, obrázok a dve čísla.
            ->without('favorites')
            ->select(['id', 'title', 'slug', 'avatar', 'front_listed_at', 'front_position'])
            // Zverejnené príspevky, nie všetky: pôvodný dopyt ukazoval pri
            // ECAV 3190 a pri Slovenskom dohovore 6348, čo boli počty
            // vrátane toho, čo ešte čaká v bufferi.
            ->withCount(['posts as posts_count' => fn ($query) => $query->published()])
            ->withMax(['posts as last_post_at' => fn ($query) => $query->published()], 'created_at');
    }
}
