<?php

namespace Tests\Support;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Falošné YouTube Data API pre App\Services\Youtube\YoutubeApi. Trasa je
 * endpoint (`playlistItems`, `videos`, …) a obsluha dostane query dopytu —
 * jeden endpoint tak môže odpovedať podľa `playlistId` či `pageToken`.
 */
trait FakesYoutube
{
    /**
     * @param  array<string, callable(array): mixed|array>  $routes
     */
    protected function fakeYoutube(array $routes): void
    {
        Http::preventStrayRequests();

        Http::fake(function (Request $request) use ($routes) {
            $endpoint = Str::after((string) parse_url($request->url(), PHP_URL_PATH), '/youtube/v3/');
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            $handler = $routes[$endpoint] ?? ['items' => []];
            $result = is_callable($handler) ? $handler($query) : $handler;

            return is_array($result) ? Http::response($result) : $result;
        });
    }

    protected function youtubeError(int $status, string $reason)
    {
        return Http::response([
            'error' => [
                'code' => $status,
                'message' => 'YouTube error',
                'errors' => [['reason' => $reason]],
            ],
        ], $status);
    }

    protected function playlistPage(array $videoIds, ?string $nextPageToken = null): array
    {
        return array_filter([
            'items' => array_map(fn ($id) => ['contentDetails' => ['videoId' => $id]], $videoIds),
            'nextPageToken' => $nextPageToken,
        ], fn ($value) => $value !== null);
    }

    protected function videoResource(string $id, array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => $id,
            'snippet' => [
                'title' => 'Video ' . $id,
                'description' => 'Popis videa',
                'publishedAt' => '2026-09-10T08:00:00Z',
                'liveBroadcastContent' => 'none',
            ],
            'contentDetails' => ['duration' => 'PT12M3S'],
            'status' => ['embeddable' => true, 'privacyStatus' => 'public'],
            'statistics' => ['commentCount' => '0'],
        ], $overrides);
    }

    /** Odpoveď videos.list pre ID z query (videá mimo $videos vynechá). */
    protected function videosFrom(array $videos): callable
    {
        return fn (array $query) => [
            'items' => array_values(array_filter(array_map(
                fn ($id) => $videos[$id] ?? null,
                explode(',', $query['id'] ?? '')
            ))),
        ];
    }

    protected function youtubeRequests(string $endpoint): \Illuminate\Support\Collection
    {
        return collect(Http::recorded())
            ->map(fn ($pair) => $pair[0])
            ->filter(fn (Request $request) => str_contains($request->url(), '/youtube/v3/' . $endpoint . '?'))
            ->values();
    }
}
