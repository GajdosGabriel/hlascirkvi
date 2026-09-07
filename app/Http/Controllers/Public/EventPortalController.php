<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\EventPortal\EventPortalClient;
use App\Services\EventPortal\RemoteEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Verejný výpis a detail podujatí na /akcie.
 *
 * Dáta neprichádzajú z našej databázy, ale z portálu event.hlascirkvi.sk —
 * viď App\Services\EventPortal. Pôvodný Public\EventController nad lokálnou
 * tabuľkou `events` zostáva zatiaľ nedotknutý pre archív na /akcie-archiv.
 */
class EventPortalController extends Controller
{
    /** Zoznamy, ktoré API pozná. Čokoľvek iné spadne na `upcoming`. */
    protected const LISTS = ['upcoming', 'ongoing', 'past', 'all'];

    /** Podoby výpisu: časová os (predvolená), stena plagátov a mapa. */
    protected const VIEWS = ['os', 'plagaty', 'mapa'];

    /**
     * Mapa s dvadsiatimi špendlíkmi nemá zmysel, preto si pýta viac podujatí
     * naraz. Sto je zároveň strop, ktorý povoľuje API portálu.
     */
    protected const MAP_PER_PAGE = 100;

    public function __construct(protected EventPortalClient $portal)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $view = in_array($request->query('view'), self::VIEWS, true)
            ? $request->query('view')
            : 'os';

        $events = $this->portal->events(
            $filters,
            (int) $request->query('page', 1),
            $view === 'mapa' ? self::MAP_PER_PAGE : null
        );

        // Najbližšie podujatie dostane veľký úvodný panel — ale len na prvej
        // strane nefiltrovaného výpisu. Vo výsledkoch hľadania by „vybrané
        // podujatie" bolo len náhodne prvý riadok, čo nikomu nepomôže.
        //
        // Na mape a na stene plagátov sa panel nezobrazuje: odsunul by ich
        // pod okraj obrazovky a na mape by navyše chýbal jeden špendlík.
        $featured = null;
        $items = collect($events->items());

        if ($view === 'os' && $this->isPlainFirstPage($request, $filters) && $items->isNotEmpty()) {
            $featured = $items->first();
            $items = $items->slice(1)->values();
        }

        return view('events.portal.index', [
            'events' => $events,
            'items' => $items,
            'days' => $this->groupByDay($items),
            'featured' => $featured,
            'mapPoints' => $view === 'mapa' ? $this->mapPoints($items) : [],
            'filters' => $filters,
            'view' => $view,
            'activeTags' => $this->activeTags($request),
            'municipalities' => $this->portal->municipalities($filters['list'] === 'past' ? 'all' : 'planned'),
            'tagGroups' => $this->portal->tagGroups(),
            'stale' => $this->portal->isStale(),
            'portalUrl' => config('eventportal.url'),
        ]);
    }

    public function show(Request $request, int $event, ?string $slug = null)
    {
        $item = $this->portal->event($event);

        if (! $item) {
            abort(404);
        }

        // Kanonická adresa je /akcie/{id}/{slug}. Starý alebo chýbajúci slug
        // presmerujeme, nech tú istú stránku neindexuje Google dvakrát.
        if ($slug !== $item->slug()) {
            return redirect()->route('event.show', [$item->id(), $item->slug()], 301);
        }

        return view('events.portal.show', [
            'event' => $item,
            'related' => $this->related($item),
            'portalUrl' => config('eventportal.url'),
        ]);
    }

    /* --------------------------------------------------------------- pomocné */

    /**
     * Filtre z URL preložené do parametrov API.
     *
     * @return array<string, mixed>
     */
    protected function filters(Request $request): array
    {
        $list = $request->query('list');

        return [
            'list' => in_array($list, self::LISTS, true) ? $list : 'upcoming',
            'search' => trim((string) $request->query('search', '')) ?: null,
            'municipality' => trim((string) $request->query('municipality', '')) ?: null,
            'tags' => $this->activeTags($request) !== [] ? implode(',', $this->activeTags($request)) : null,
            'range' => $request->query('range') === 'weekend' ? 'weekend' : null,
        ];
    }

    /**
     * Zaškrtnuté štítky. V URL sú ako ?tags=koncert,folklor — rovnako ako ich
     * čaká API, takže odkazy sa dajú skladať bez prekladu.
     *
     * @return array<int, string>
     */
    protected function activeTags(Request $request): array
    {
        $raw = $request->query('tags');
        $raw = is_array($raw) ? $raw : explode(',', (string) $raw);

        return array_values(array_unique(array_filter(
            array_map(static fn ($tag) => trim((string) $tag), $raw)
        )));
    }

    /** @param array<string, mixed> $filters */
    protected function isPlainFirstPage(Request $request, array $filters): bool
    {
        return (int) $request->query('page', 1) === 1
            && $filters['list'] === 'upcoming'
            && $filters['search'] === null
            && $filters['municipality'] === null
            && $filters['tags'] === null
            && $filters['range'] === null;
    }

    /**
     * Podujatia zoskupené podľa dňa začiatku — z toho vzniká časová os.
     *
     * @param  Collection<int, RemoteEvent>  $items
     * @return Collection<string, Collection<int, RemoteEvent>>
     */
    protected function groupByDay(Collection $items): Collection
    {
        return $items->groupBy(fn (RemoteEvent $event) => $event->dayKey());
    }

    /**
     * Špendlíky pre mapu — jeden na miesto, nie na podujatie.
     *
     * V tom istom kostole alebo pastoračnom centre býva podujatí viac a ich
     * značky by na mape ležali presne na sebe: bolo by vidieť len tú vrchnú
     * a zvyšok by sa nedal ani rozkliknúť. Preto sa podujatia s rovnakými
     * súradnicami zlúčia do jedného špendlíka a vypíšu sa v jeho bubline.
     *
     * Podujatia bez súradníc (typicky miesto „Celé Slovensko" alebo import
     * bez adresy) sa na mapu nedostanú — koľko ich je, hovorí šablóna, aby
     * si nikto nemyslel, že mapa ukazuje menej než zoznam bez dôvodu.
     *
     * @param  Collection<int, RemoteEvent>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mapPoints(Collection $items): array
    {
        $points = [];

        foreach ($items as $event) {
            $latitude = $event->latitude();
            $longitude = $event->longitude();

            if ($latitude === null || $longitude === null) {
                continue;
            }

            // Zaokrúhlenie na päť desatinných miest (~1 m) spoľahlivo spojí
            // záznamy, ktoré geokóder uložil s odlišným počtom desatinných
            // miest, a nespojí dve naozaj rôzne miesta.
            $key = round($latitude, 5) . ',' . round($longitude, 5);

            if (! isset($points[$key])) {
                $points[$key] = [
                    'lat' => $latitude,
                    'lng' => $longitude,
                    'municipality' => $event->municipality(),
                    'venues' => [],
                    'events' => [],
                ];
            }

            if ($event->venue()) {
                $points[$key]['venues'][$event->venue()] = true;
            }

            $points[$key]['events'][] = [
                'title' => $event->title(),
                'url' => $event->url(),
                'date' => $event->dateRangeLabel(),
                'venue' => $event->venue(),
            ];
        }

        return array_values(array_map(static function (array $point) {
            $venues = array_keys($point['venues']);
            unset($point['venues']);

            // Geokóder vracia pre viacero adries v jednom meste občas ten istý
            // bod — v Nitre tak na jednom špendlíku sedia štyri rôzne miesta.
            // Pomenovať ho podľa prvého z nich by o zvyšku klamalo, takže
            // nadpis dostane obec a názov miesta si nesie každé podujatie.
            $point['venue'] = count($venues) === 1 ? $venues[0] : null;

            return $point;
        }, $points));
    }

    /**
     * „Ďalšie podujatia v okolí" pod detailom.
     *
     * Ide o druhé volanie API, preto zámerne krátky zoznam a iba vtedy, keď
     * podujatie vieme niekam zaradiť. Samo seba zo zoznamu vyhodíme.
     *
     * @return Collection<int, RemoteEvent>
     */
    protected function related(RemoteEvent $event): Collection
    {
        $slug = $event->municipalitySlug();

        if (! $slug) {
            return collect();
        }

        $paginator = $this->portal->events(
            ['list' => 'upcoming', 'municipality' => $slug],
            1,
            5
        );

        return collect($paginator->items())
            ->reject(fn (RemoteEvent $other) => $other->id() === $event->id())
            ->take(4)
            ->values();
    }
}
