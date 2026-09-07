<?php

namespace App\Services\EventPortal;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $data = $this->get('/api/events/' . $id, [], (int) config('eventportal.ttl.show'));

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
        $staleKey = $key . ':stale';

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('eventportal.timeout', 8))
                ->withHeaders(['User-Agent' => 'hlascirkvi.sk (event portal reader)'])
                ->get(config('eventportal.url') . $path, $query);

            // 404 je platná odpoveď (zmazané podujatie), nie výpadok — nemá
            // zmysel na ňu ponúkať starú kópiu, detail má skončiť na 404.
            if ($response->status() === 404) {
                Cache::put($key, [], $ttl);

                return [];
            }

            $data = $response->throw()->json();

            if (! is_array($data)) {
                throw new \RuntimeException('Neočakávaná odpoveď API.');
            }

            Cache::put($key, $data, $ttl);
            Cache::put($staleKey, $data, (int) config('eventportal.stale_ttl'));

            return $data;
        } catch (ConnectionException | \Throwable $e) {
            Log::warning('Event portál nedostupný: ' . $e->getMessage(), [
                'path' => $path,
                'query' => $query,
            ]);

            $fallback = Cache::get($staleKey);

            if (is_array($fallback)) {
                $this->stale = true;

                return $fallback;
            }

            return [];
        }
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

    /** @param array<string, mixed> $query */
    protected function key(string $path, array $query): string
    {
        ksort($query);

        return self::PREFIX . ':' . sha1($path . '?' . http_build_query($query));
    }
}
