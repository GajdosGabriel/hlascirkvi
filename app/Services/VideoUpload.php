<?php

namespace App\Services;

use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\DisableImport;
use App\Services\Youtube\NotifyAdmin;
use App\Services\Youtube\PlaylistId;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\YoutubeApi;
use App\Services\Youtube\YoutubeApiException;
use Illuminate\Support\Facades\Log;

/**
 * Denný import videí z kanálov a playlistov YouTube.
 *
 * Kanál sa číta cez jeho uploads playlist (UC… → UU…), po päťdesiatich
 * a so stránkovaním. Predtým to bol activities.list s predvolenými piatimi
 * položkami — kanál, ktorý medzi dvoma behmi nahral viac videí, o zvyšok
 * prišiel.
 */
class VideoUpload
{
    /**
     * Stránok po päťdesiat na jeden zdroj. Beh končí skôr, len čo narazí na
     * známe video — viac stránok prejde len kanál, ktorému import dlho stál.
     */
    private const MAX_PAGES = 4;

    /**
     * Kanál bez jediného príspevku si prvýkrát stiahne len toľkoto najnovších
     * videí. Buffer púšťa kanál, z ktorého ešte nič nevyšlo, prednostne —
     * dvesto videí nového kanála by titulku ovládlo na týždne.
     */
    private const FIRST_IMPORT = 20;

    protected EloquentCanalRepository $organizations;

    protected YoutubeApi $api;

    protected VideoImporter $importer;

    public function __construct(?YoutubeApi $api = null)
    {
        $this->organizations = new EloquentCanalRepository;
        $this->api = $api ?? app(YoutubeApi::class);
        $this->importer = new VideoImporter($this->api);
    }

    /**
     * @return int počet nových videí
     */
    public function handle(?int $canalId = null): int
    {
        // Zoznam už rozposlaných hlásení patrí jednému behu importu.
        NotifyAdmin::forget();

        return $this->foreachOrganization($canalId);
    }

    /**
     * Jeden nedostupný kanál ani zmazaný playlist nesmú zhodiť celý beh —
     * preto je každý kanál v samostatnom try/catch. Vyčerpaná kvóta je
     * výnimka: zvyšné kanály by zlyhali rovnako, beh sa preto ukončí.
     */
    protected function foreachOrganization(?int $canalId): int
    {
        $saved = 0;

        $organizations = $this->organizations->getYoutubeVideos()
            ->when($canalId, fn ($canals) => $canals->where('id', $canalId));

        foreach ($organizations as $organization) {
            try {
                $saved += $this->validateUrlPlaylistOrChannel($organization);
            } catch (\Throwable $e) {
                Log::warning('Import videí z YouTube zlyhal: ' . $e->getMessage(), [
                    'organization_id' => $organization->id,
                    'channel' => $organization->youtube_channel,
                    'playlist' => $organization->youtube_playlist,
                ]);

                if ($e instanceof YoutubeApiException && $e->is('quotaExceeded')) {
                    break;
                }
            }
        }

        return $saved;
    }

    /**
     * Kanál môže mať zadaný kanál na YouTube, playlist alebo oboje — videá
     * z oboch zdrojov sa zlúčia. Keď sa ani jeden zo zadaných zdrojov na
     * YouTube nenájde, sťahovanie sa kanálu vypne a superadminovi príde
     * notifikácia.
     */
    protected function validateUrlPlaylistOrChannel($organization): int
    {
        $sources = [];

        if (trim((string) $organization->youtube_channel) !== '') {
            $sources[] = $this->fromChannel($organization);
        }

        if (trim((string) $organization->youtube_playlist) !== '') {
            $sources[] = $this->fromPlaylist($organization);
        }

        if ($sources === []) {
            return 0;
        }

        $missing = array_values(array_filter(array_column($sources, 'missing')));

        // Vypíname len kanál, ktorému nezostal žiadny funkčný zdroj. Keď mu
        // druhý zdroj beží, import má odkiaľ brať a stačí notifikácia.
        if (count($missing) === count($sources)) {
            DisableImport::because($organization, implode('; ', $missing));

            return 0;
        }

        // Notifikácia, nie log: chýbajúci zdroj je preklep alebo starý údaj
        // vo formulári kanála a opraviť ho vie len správca.
        foreach ($missing as $reason) {
            NotifyAdmin::about(
                $organization,
                'source-missing',
                $organization->title . ': ' . $reason
                    . '. Videá zatiaľ chodia z druhého zdroja — údaj opravte alebo vymažte vo formulári kanála.'
            );
        }

        $ids = array_merge(...array_map(fn ($source) => $source['videos'] ?? [], $sources));

        return count($this->importer->import($organization, $ids));
    }

    /**
     * Videá z kanála. Adresu kanála (https://www.youtube.com/@meno) prepíše
     * na ID a uloží — formulár to už robí sám, v databáze však staré hodnoty
     * zostali.
     *
     * @return array{videos: ?array, missing: ?string}
     */
    protected function fromChannel($organization): array
    {
        $raw = trim((string) $organization->youtube_channel);
        $channelId = ChannelId::fromInput($raw);

        if ($channelId === null) {
            $channelId = ChannelId::resolve($raw);

            if ($channelId === null) {
                return ['videos' => null, 'missing' => 'kanál „' . $raw . '" sa na YouTube nenašiel'];
            }

            $organization->forceFill(['youtube_channel' => $channelId])->save();

            NotifyAdmin::about(
                $organization,
                'channel-rewritten',
                $organization->title . ': adresa kanála YouTube „' . $raw . '" bola prepísaná na ID '
                    . $channelId . '. Skontrolujte vo formulári kanála, či ide o správny kanál.'
            );
        }

        try {
            return ['videos' => $this->playlistVideoIds(YoutubeApi::uploadsPlaylistId($channelId), $organization), 'missing' => null];
        } catch (YoutubeApiException $e) {
            if ($e->is('quotaExceeded')) {
                throw $e;
            }

            // Či kanál naozaj zmizol, povie až channels.list.
            if ($this->channelIsGone($channelId)) {
                return ['videos' => null, 'missing' => 'kanál ' . $channelId . ' na YouTube už neexistuje'];
            }

            // Kanál bez jediného videa nemá ani uploads playlist.
            if ($e->is('playlistNotFound')) {
                return ['videos' => [], 'missing' => null];
            }

            throw $e;
        }
    }

    /**
     * @return array{videos: ?array, missing: ?string}
     */
    protected function fromPlaylist($organization): array
    {
        $raw = trim((string) $organization->youtube_playlist);
        $playlistId = PlaylistId::fromInput($raw);

        if ($playlistId === null) {
            return ['videos' => null, 'missing' => 'playlist „' . $raw . '" nie je ID playlistu'];
        }

        try {
            return ['videos' => $this->playlistVideoIds($playlistId, $organization), 'missing' => null];
        } catch (YoutubeApiException $e) {
            // Zmazaný playlist YouTube pomenuje priamo.
            if ($e->is('playlistNotFound')) {
                return ['videos' => null, 'missing' => 'playlist ' . $playlistId . ' na YouTube už neexistuje'];
            }

            throw $e;
        }
    }

    /**
     * ID videí z playlistu, od najnovšieho. Stránka, na ktorej je už známe
     * video, je posledná — staršie videá sú stiahnuté tiež.
     *
     * @return string[]
     */
    protected function playlistVideoIds(string $playlistId, $organization): array
    {
        if (! $organization->posts()->withTrashed()->withoutGlobalScopes()->exists()) {
            $items = $this->api->playlistItems($playlistId, null, self::FIRST_IMPORT)->items;

            return array_values(array_filter(array_map([VideoId::class, 'from'], $items)));
        }

        $ids = [];
        $token = null;

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $response = $this->api->playlistItems($playlistId, $token);
            $pageIds = array_values(array_filter(array_map([VideoId::class, 'from'], $response->items)));

            $ids = array_merge($ids, $pageIds);
            $token = $response->nextPageToken;

            if ($token === null || $pageIds === [] || VideoImporter::existingIds($pageIds) !== []) {
                break;
            }
        }

        return $ids;
    }

    protected function channelIsGone(string $channelId): bool
    {
        try {
            return ! $this->api->channelExists($channelId);
        } catch (\Throwable $e) {
            // Vyčerpaná kvóta ani výpadok API neznamenajú zmazaný kanál —
            // v takom prípade import radšej nevypíname.
            return false;
        }
    }
}
