<?php

namespace App\View\Components\Filters;

use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

/**
 * Lišta filtrov nad výpisom.
 *
 * Nahrádza pôvodné x-filters.card + x-filters.<meno>, kde si každý filter
 * niesol vlastnú triedu a v nej natvrdo zoznam route names, na ktorých sa smie
 * zobraziť. Pridať filter na stránku tak znamenalo upravovať PHP triedu inde
 * v projekte. Tu si stránka povie sama, čo chce:
 *
 *     <x-filters.bar :filters="['unpublished', 'deletedAt']" search="Hľadať kanál" />
 *
 * Kľúče sú tie isté, aké číta App\Filters\Filters::getFilters(), takže lišta
 * sedí na ľubovoľný výpis, ktorý ide cez ->filter($filters).
 */
class Bar extends Component
{
    /** Preddefinované popisy, aby sa pri bežných filtroch nemuseli písať. */
    public const LABELS = [
        'unpublished'    => 'Nepublikované',
        'deletedAt'      => 'Zrušené',
        'banned'         => 'Zablokovaní',
        'fulfilled'      => 'Vypočuté',
        'videoAvailable' => 'Nedostupné video',
    ];

    /** Prepínače v tvare [kľúč v query stringu => popis na tlačidle]. */
    public array $options = [];

    /** Placeholder hľadania; null znamená, že výpis hľadanie nemá. */
    public ?string $search;

    /**
     * @param  array<int|string, string>  $filters  ['unpublished', 'deletedAt' => 'Vymazané']
     */
    public function __construct(array $filters = [], ?string $search = null)
    {
        foreach ($filters as $key => $label) {
            if (is_int($key)) {
                $key = $label;
                $label = self::LABELS[$key] ?? ucfirst($key);
            }

            $this->options[$key] = $label;
        }

        $this->search = $search;
    }

    public function render()
    {
        return view('components.filters.bar');
    }

    /**
     * Rovnaká pravdivosť ako v App\Filters\Filters::getFilters(), ktorá hodnoty
     * preosieva cez array_filter — inak by sa ?unpublished=0 kreslilo ako
     * zapnutý prepínač, hoci výpis ho ignoruje.
     */
    public function isOn(string $key): bool
    {
        return (bool) request()->query($key);
    }

    /** Zapne alebo vypne jeden prepínač, ostatné parametre ostávajú. */
    public function toggleUrl(string $key): string
    {
        return $this->urlWith([$key => $this->isOn($key) ? null : 'true']);
    }

    public function searchTerm(): string
    {
        return trim((string) request()->query('search', ''));
    }

    public function clearSearchUrl(): string
    {
        return $this->urlWith(['search' => null]);
    }

    /** Hľadanie je bežný GET formulár, ostatné filtre teda musí niesť so sebou. */
    public function hiddenFields(): array
    {
        return array_diff_key($this->activeQuery(), ['search' => null]);
    }

    public function anyActive(): bool
    {
        return $this->activeQuery() !== [];
    }

    public function resetUrl(): string
    {
        return URL::current();
    }

    /**
     * Zo súčasnej adresy postaví novú so zmenenými parametrami. Hodnota null
     * parameter odstráni. Stránkovanie sa vždy zahodí — po zmene filtra by
     * ukazovalo na stránku, ktorá už v novom výsledku nemusí existovať.
     */
    protected function urlWith(array $changes): string
    {
        $params = array_merge($this->activeQuery(), $changes);
        $params = array_filter($params, fn ($value) => $value !== null && $value !== '');

        return $params ? URL::current().'?'.http_build_query($params) : URL::current();
    }

    /**
     * Parametre, ktoré lišta spravuje. Adresy si stavia len z nich, takže cudzí
     * query string (vrátane ?page) sa pri zmene filtra zahodí.
     */
    protected function activeQuery(): array
    {
        $active = [];

        foreach (array_keys($this->options) as $key) {
            if ($this->isOn($key)) {
                $active[$key] = request()->query($key);
            }
        }

        if ($this->searchTerm() !== '') {
            $active['search'] = $this->searchTerm();
        }

        return $active;
    }
}
