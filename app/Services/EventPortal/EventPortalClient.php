<?php

namespace App\Services\EventPortal;

use App\Services\SystemLog\Recorder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Čítanie podujatí z verejného API portálu event.hlascirkvi.sk.
 *
 * Nič sa neukladá do databázy. Každá odpoveď ide do cache na pár minút
 * a súčasne do dlhšej záložnej kópie: keď portál vypadne, návštevník uvidí
 * poslednú známu odpoveď namiesto prázdnej stránky alebo chyby 500.
 */
class EventPortalClient
{
    /** Prefix všetkých kľúčov v cache — dá sa tak vyprázdniť naraz. */
    protected const PREFIX = 'eventportal';

    /** Nastaví sa, keď niektoré volanie muselo siahnuť po záložnej kópii. */
    protected bool $stale = false;

    /** Nastaví sa, keď volanie zlyhalo a nebola po ruke ani záložná kópia. */
    protected bool $unavailable = false;

    /**
     * Výpis podujatí ako stránkovač, aby fungovali bežné {{ $events->links() }}.
     *
     * @param  array<string, mixed>  $filters  Filtre podľa API (list, search,
     *                                         municipality, tags, range…).
     * @return LengthAwarePaginator<int, RemoteEvent>
     */
    public function events(array $filters = [], int $page = 1, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?: (int) config('eventportal.per_page', 20);

        $filters = $this->normalizeEventFilters($filters);

        $query = array_merge($this->clean($filters), [
            'per_page' => $perPage,
            'page' => max(1, $page),
        ]);

        $response = $this->get('/api/events', $query, (int) config('eventportal.ttl.index'));

        $items = collect($response['data'] ?? [])
            ->map(fn (array $row) => RemoteEvent::make($row))
            ->all();

        $total = (int) ($response['meta']['total'] ?? count($items));

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            max(1, $page),
            [
                // Odkazy stránkovania musia smerovať na náš výpis, nie na API,
                // a musia si niesť aktívne filtre.
                'path' => route('akcie.index'),
                'query' => request()->except('page'),
            ]
        );
    }

    /** Detail podujatia. Vráti null, keď neexistuje alebo je portál nedostupný. */
    public function event(int $id): ?RemoteEvent
    {
        $this->unavailable = false;

        $data = $this->get('/api/events/'.$id,[], (int) config('eventportal.ttl.show'));

        if (! is_array($data) || empty($data['id'])) {
            return null;
        }

        return RemoteEvent::make($data);
    }

    /**
     * Číselník obsahových štítkov, zoskupený podľa druhu (formát, téma…).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function tagGroups(): Collection
    {
        $data = $this->get('/api/tags', [], (int) config('eventportal.ttl.taxonomy'));

        return collect($data['data'] ?? []);
    }

    /**
     * Obce s počtom podujatí — pre filter v bočnom paneli.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function municipalities(string $scope = 'planned'): Collection
    {
        $data = $this->get(
            '/api/events/municipalities-overview',
            ['scope' => $scope],
            (int) config('eventportal.ttl.taxonomy')
        );

        return collect($data['data'] ?? [])
            ->filter(fn ($row) => is_array($row) && ($row['events_count'] ?? 0) > 0)
            ->values();
    }

    /**
     * Museli sme v tejto požiadavke siahnuť po záložnej kópii? Výpis podľa
     * toho zobrazí hlášku, že portál je nedostupný a dáta sú staršie.
     */
    public function isStale(): bool
    {
        return $this->stale;
    }

    /**
     * Skončilo posledné volanie event() naprázdno preto, že portál neodpovedal
     * a záložná kópia neexistuje? Detail vtedy nesmie vrátiť 404 — podujatie
     * existuje, len ho práve nevieme načítať.
     */
    public function isUnavailable(): bool
    {
        return $this->unavailable;
    }

    /* ------------------------------------------------------------------ HTTP */

    /**
     * Jedno GET volanie s cache a záložnou kópiou.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query, int $ttl): array
    {
        $key = $this->key($path, $query);
        $staleKey = $key.':stale';

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        // Portál nás pred chvíľou odmietol (429) alebo nežil — ďalšie volania
        // by ho len dobíjali. Kým pauza trvá, ide sa rovno po záložnej kópii.
        if (Cache::has(self::PREFIX.':cooldown')) {
            return $this->fallback($staleKey);
        }

        // Verejný filter dokáže vytvoriť prakticky neobmedzený počet URL a
        // každá dovtedy nevidená kombinácia obíde cache. Držíme preto aj
        // spoločný strop odchádzajúcich volaní, nižší než limit portálu.
        // Po jeho dosiahnutí návštevník dostane záložnú odpoveď a portál sa
        // nedostane do stavu 429 ani pri distribuovanom prechádzaní filtrov.
        //
        // Kombinácie štítkov a hľadanie majú navyše vlastný, menší strop:
        // prechádza ich najmä robot a bez neho by minul spoločný rozpočet
        // skôr, než sa dostane na rad detail podujatia či základný výpis.
        $outboundLimit = (int) config('eventportal.outbound_limit', 120);
        $filterLimit = $this->isFilterCombination($path, $query)
            ? (int) config('eventportal.filter_limit', 30)
            : 0;

        if ($filterLimit > 0 && RateLimiter::tooManyAttempts(self::PREFIX.':outbound:filters', $filterLimit)) {
            return $this->fallback($staleKey);
        }

        if ($outboundLimit > 0) {
            $limiterKey = self::PREFIX.':outbound';

            if (RateLimiter::tooManyAttempts($limiterKey, $outboundLimit)) {
                return $this->fallback($staleKey);
            }

            RateLimiter::hit($limiterKey, 60);
        }

        if ($filterLimit > 0) {
            RateLimiter::hit(self::PREFIX.':outbound:filters', 60);
        }

        // Po skončení pauzy zavolajú portál všetky práve bežiace požiadavky
        // naraz a každá dostane tú istú chybu. Do logu ju zapíše len tá,
        // ktorá pauzu vyhlásila.
        $report = true;

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('eventportal.timeout', 3))
                ->connectTimeout((int) config('eventportal.connect_timeout', 2))
                // X-Locale: texty, ktoré portál posiela hotové (napr. ticket_cta),
                // majú prísť po slovensky, nie v predvolenom jazyku API.
                ->withHeaders(['User-Agent' => 'hlascirkvi.sk (event portal reader)', 'X-Locale' => 'sk'])
                ->get(config('eventportal.url').$path, $query);

            // 404 je platná odpoveď (zmazané podujatie), nie výpadok — nemá
            // zmysel na ňu ponúkať starú kópiu, detail má skončiť na 404.
            if ($response->status() === 404) {
                Cache::put($key, [], $ttl);

                return [];
            }

            // Pri 429 a 5xx sa na chvíľu odmlčíme. Retry-After má prednosť,
            // no najviac 10 minút, nech jedna čudná hlavička neodstaví výpis.
            if ($response->status() === 429 || $response->serverError()) {
                $retryAfter = (int) $response->header('Retry-After');

                $report = $this->cooldown($retryAfter > 0
                    ? min($retryAfter, 600)
                    : (int) config('eventportal.cooldown', 60));
            }

            $data = $response->throw()->json();

            if (! is_array($data)) {
                throw new \RuntimeException('Neočakávaná odpoveď API.');
            }

            Cache::put($key, $data, $ttl);
            Cache::put($staleKey, $data, (int) config('eventportal.stale_ttl'));

            return $data;
        } catch (ConnectionException|\Throwable $e) {
            if ($e instanceof ConnectionException) {
                $report = $this->cooldown((int) config('eventportal.cooldown', 60));
            }

            // Telo chybovej odpovede (celá HTML stránka) do logu nepatrí.
            if ($report) {
                Log::warning('Event portál nedostupný: '.strtok($e->getMessage(), "\n"), [
                    'path' => $path,
                    'query' => $query,
                ]);
            }

            // Do denníka najviac raz za hodinu — padá to pri každom zobrazení.
            if (Recorder::onceIn(60, 'event-portal-down')) {
                Recorder::warning('portal', 'unavailable', 'Event portál nedostupný: '.strtok($e->getMessage(), "\n"),
                    status: 'failed',
                    context: ['path' => $path, 'query' => $query],
                );
            }

            return $this->fallback($staleKey);
        }
    }

    /** Posledná úspešná odpoveď, alebo prázdne pole, keď žiadna nie je. */
    protected function fallback(string $staleKey): array
    {
        $fallback = Cache::get($staleKey);

        if (is_array($fallback)) {
            $this->stale = true;

            return $fallback;
        }

        $this->unavailable = true;

        return [];
    }

    /**
     * Zastaví volania API na daný počet sekúnd pre všetky požiadavky naraz.
     * Vráti false, keď pauza už beží — vyhlásila ju iná požiadavka.
     */
    protected function cooldown(int $seconds): bool
    {
        return Cache::add(self::PREFIX.':cooldown', true, max(1, $seconds));
    }

    /**
     * Výpis zúžený viacerými štítkami alebo hľadaním — priestor, ktorý sa dá
     * kombinovať donekonečna. Jeden štítok či obec je konečný zoznam adries.
     *
     * @param  array<string, mixed>  $query
     */
    protected function isFilterCombination(string $path, array $query): bool
    {
        return $path === '/api/events'
            && (isset($query['search']) || str_contains((string) ($query['tags'] ?? ''), ','));
    }

    /**
     * Zahodí prázdne filtre, nech tá istá stránka nekončí pod dvomi kľúčmi
     * v cache len preto, že raz prišla s ?search= a raz bez neho.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function clean(array $filters): array
    {
        return array_filter(
            $filters,
            static fn ($value) => $value !== null && $value !== '' && $value !== []
        );
    }

    /**
     * Zjednotí filtre ešte pred zostavením cache kľúča a HTTP požiadavky.
     * Poradie rovnakých tagov tak nevyrába nové cache položky a podvrhnuté
     * parametre ani neprimerane dlhé zoznamy sa na portál neposielajú.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function normalizeEventFilters(array $filters): array
    {
        $filters = array_intersect_key($filters, array_flip([
            'list',
            'search',
            'municipality',
            'tags',
            'range',
        ]));

        if (isset($filters['search'])) {
            $filters['search'] = mb_substr(trim((string) $filters['search']), 0, 100);
        }

        if (isset($filters['municipality'])) {
            $municipality = trim((string) $filters['municipality']);
            $filters['municipality'] = preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D', $municipality)
                && strlen($municipality) <= 100
                    ? $municipality
                    : null;
        }

        if (isset($filters['tags'])) {
            $tags = is_array($filters['tags'])
                ? $filters['tags']
                : explode(',', (string) $filters['tags']);

            $tags = array_values(array_unique(array_filter(
                array_map(static fn ($tag) => trim((string) $tag), $tags),
                static fn (string $tag) => strlen($tag) <= 64
                    && preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D', $tag) === 1
            )));

            sort($tags, SORT_STRING);
            $tags = array_slice($tags, 0, max(1, (int) config('eventportal.max_tags', 10)));
            $filters['tags'] = $tags === [] ? null : implode(',', $tags);
        }

        return $this->clean($filters);
    }

    /** @param array<string, mixed> $query */
    protected function key(string $path, array $query): string
    {
        ksort($query);

        return self::PREFIX.':'.sha1($path.'?'.http_build_query($query));
    }
}
