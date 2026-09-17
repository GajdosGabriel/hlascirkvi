<?php

namespace App\Services\Youtube;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Tenký klient YouTube Data API v3 nad Laravel Http.
 *
 * Nahrádza balík alaouy/youtube, ktorý volal curl bez timeoutu a retry,
 * nevedel stránkovať komentáre ani zúžiť odpoveď cez `fields` a pri prázdnej
 * odpovedi vracal `false` namiesto prázdneho poľa.
 *
 * Kvóta (jednotky): channels, playlistItems, videos, commentThreads po 1,
 * search 100. Odpovede sú objekty (stdClass) — v takom tvare ich čítajú
 * VideoId aj YoutubeThumbnail.
 */
class YoutubeApi
{
    private const BASE = 'https://www.googleapis.com/youtube/v3/';

    /** Maximum ID na jeden dopyt videos.list / channels.list. */
    public const BATCH = 50;

    private string $key;

    public function __construct(?string $key = null)
    {
        $this->key = (string) ($key ?? config('youtube.key'));
    }

    /**
     * Uploads playlist kanála: UC… → UU…. Stojí nula jednotiek, channels.list
     * s contentDetails by stál jednu a vrátil to isté.
     */
    public static function uploadsPlaylistId(string $channelId): string
    {
        return 'UU' . substr($channelId, 2);
    }

    public function channelIdByHandle(string $handle): ?string
    {
        return $this->firstId('channels', ['forHandle' => '@' . ltrim($handle, '@'), 'part' => 'id']);
    }

    public function channelIdByUsername(string $username): ?string
    {
        return $this->firstId('channels', ['forUsername' => $username, 'part' => 'id']);
    }

    /** Posledná možnosť — search stojí sto jednotiek. */
    public function searchChannelId(string $query): ?string
    {
        $response = $this->get('search', [
            'q' => $query,
            'type' => 'channel',
            'part' => 'id',
            'maxResults' => 1,
            'fields' => 'items/id/channelId',
        ]);

        return $response->items[0]->id->channelId ?? null;
    }

    public function channelExists(string $channelId): bool
    {
        return $this->firstId('channels', ['id' => $channelId, 'part' => 'id']) !== null;
    }

    /**
     * @return array<string, object> kanály podľa ID
     */
    public function channels(array $ids, string $part = 'snippet,statistics'): array
    {
        return $this->byId('channels', $ids, $part);
    }

    /**
     * Jedna stránka playlistu. Položky nesú `contentDetails.videoId`
     * a `contentDetails.videoPublishedAt`.
     *
     * @return object{items: array, nextPageToken: ?string}
     */
    public function playlistItems(string $playlistId, ?string $pageToken = null, int $maxResults = 50): object
    {
        $response = $this->get('playlistItems', array_filter([
            'playlistId' => $playlistId,
            'part' => 'contentDetails',
            'maxResults' => $maxResults,
            'pageToken' => $pageToken,
            'fields' => 'nextPageToken,items/contentDetails(videoId,videoPublishedAt)',
        ]));

        return (object) [
            'items' => $response->items ?? [],
            'nextPageToken' => $response->nextPageToken ?? null,
        ];
    }

    /**
     * Detaily videí po dávkach. Zmazané a súkromné videá YouTube vo výsledku
     * jednoducho vynechá — volajúci ich spozná podľa chýbajúceho kľúča.
     *
     * @param  string[]  $ids
     * @return array<string, object> videá podľa ID
     */
    public function videos(array $ids, string $part = 'snippet,contentDetails,status,statistics,liveStreamingDetails'): array
    {
        return $this->byId('videos', $ids, $part);
    }

    /**
     * Vlákna komentárov k videu vrátane odpovedí, ktoré YouTube k vláknu
     * pribalí (nie všetky — ich počet nesie `snippet.totalReplyCount`).
     *
     * @return object{items: array, nextPageToken: ?string}
     */
    public function commentThreads(string $videoId, string $order = 'relevance', int $maxResults = 100, ?string $pageToken = null): object
    {
        $response = $this->get('commentThreads', array_filter([
            'videoId' => $videoId,
            'part' => 'snippet,replies',
            'order' => $order,
            'textFormat' => 'plainText',
            'maxResults' => $maxResults,
            'pageToken' => $pageToken,
        ]));

        return (object) [
            'items' => $response->items ?? [],
            'nextPageToken' => $response->nextPageToken ?? null,
        ];
    }

    /**
     * Fulltext videí naprieč YouTube (100 jednotiek).
     *
     * @return array položky search.list (ID v `id.videoId`)
     */
    public function searchVideos(string $query, int $maxResults = 30): array
    {
        return $this->get('search', [
            'q' => $query,
            'type' => 'video',
            'part' => 'id',
            'maxResults' => $maxResults,
            'fields' => 'items/id/videoId',
        ])->items ?? [];
    }

    private function firstId(string $endpoint, array $query): ?string
    {
        $id = $this->get($endpoint, $query + ['fields' => 'items/id'])->items[0]->id ?? null;

        return is_string($id) ? $id : null;
    }

    /**
     * @return array<string, object>
     */
    private function byId(string $endpoint, array $ids, string $part): array
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids))));
        $result = [];

        foreach (array_chunk($ids, self::BATCH) as $chunk) {
            $response = $this->get($endpoint, [
                'id' => implode(',', $chunk),
                'part' => $part,
                'maxResults' => self::BATCH,
            ]);

            foreach ($response->items ?? [] as $item) {
                $result[$item->id] = $item;
            }
        }

        return $result;
    }

    private function get(string $endpoint, array $query): object
    {
        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->acceptJson()
                // Opakuje sa len výpadok siete a 5xx; 403/404 sú odpoveď,
                // ktorú druhý pokus nezmení, a pri kvóte by ju ešte míňal.
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException
                    || ($e instanceof RequestException && $e->response->serverError()), throw: false)
                ->get(self::BASE . $endpoint, $query + ['key' => $this->key]);
        } catch (ConnectionException $e) {
            throw new YoutubeApiException('YouTube API nedostupné: ' . $e->getMessage(), 'connection');
        }

        if ($response->failed()) {
            $error = $response->json('error') ?? [];
            $reason = $error['errors'][0]['reason'] ?? null;
            $detail = collect($error['details'] ?? [])
                ->firstWhere('@type', 'type.googleapis.com/google.rpc.ErrorInfo')['reason'] ?? null;

            $e = new YoutubeApiException(
                sprintf('Error %d %s : %s', $response->status(), $error['message'] ?? $response->reason(), $reason ?? 'unknown'),
                $reason,
                $response->status(),
                $detail,
            );

            // Odmietnutý kľúč zastaví všetko, čo na YouTube siaha — správca
            // sa o ňom musí dozvedieť hneď, nie z logu.
            KeyFailureAlert::report($e);

            throw $e;
        }

        return $response->object() ?? (object) [];
    }
}
