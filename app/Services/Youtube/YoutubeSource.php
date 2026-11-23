<?php

namespace App\Services\Youtube;

/**
 * Jedno pole „YouTube kanál alebo playlist“ vo formulári kanála. Správca
 * vloží, čo má po ruke — @meno, adresu kanála (aj …/videos), adresu
 * playlistu alebo odkaz na hocijaké video z kanála — a odtiaľto sa určí,
 * či ide do `youtube_channel` alebo do `youtube_playlist`.
 *
 * Hľadanie kanála podľa mena sa tu nepoužíva: prvý výsledok fulltextu môže
 * byť cudzí kanál a uložil by sa bez povšimnutia.
 */
class YoutubeSource
{
    /**
     * @return array{channel: ?string, playlist: ?string}|null null, keď sa
     *         zo vstupu nedá určiť ani kanál, ani playlist
     *
     * @throws YoutubeApiException keď YouTube API odpovie chybou
     */
    public static function resolve(?string $value): ?array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return ['channel' => null, 'playlist' => null];
        }

        // Playlist má prednosť — aj odkaz na video z playlistu (watch?v=…&list=PL…)
        // správca vkladá preto, že chce práve ten playlist.
        if ($playlist = PlaylistId::fromInput($value)) {
            return ['channel' => null, 'playlist' => $playlist];
        }

        if ($channel = ChannelId::fromInput($value)) {
            return ['channel' => $channel, 'playlist' => null];
        }

        // Odkaz na video: kanál určí autor videa. Holé 11-znakové slovo by
        // mohol byť aj handle, preto len adresa.
        if (str_contains($value, '/') && ($video = VideoId::fromInput($value))) {
            $author = app(YoutubeApi::class)->videos([$video], 'snippet')[$video]->snippet->channelId ?? null;

            return ChannelId::isId($author) ? ['channel' => $author, 'playlist' => null] : null;
        }

        $channel = ChannelId::resolve($value, search: false);

        return $channel ? ['channel' => $channel, 'playlist' => null] : null;
    }

    /**
     * Kontrola pre formulár (náhľad pod poľom): nájde sa zdroj a existuje?
     *
     * @return array{ok: bool, type?: string, id?: string, url?: string, message: string}
     */
    public static function check(?string $value): array
    {
        try {
            $source = self::resolve($value);

            if ($source === null) {
                return ['ok' => false, 'message' => 'Kanál ani playlist sa podľa tohto zápisu nenašiel.'];
            }

            if ($source['channel'] === null && $source['playlist'] === null) {
                return ['ok' => false, 'message' => 'Pole je prázdne — videá sa sťahovať nebudú.'];
            }

            $api = app(YoutubeApi::class);

            if ($id = $source['channel']) {
                if (! $api->channelExists($id)) {
                    return ['ok' => false, 'message' => 'Kanál ' . $id . ' na YouTube neexistuje.'];
                }

                return [
                    'ok' => true, 'type' => 'channel', 'id' => $id,
                    'url' => 'https://www.youtube.com/channel/' . $id,
                    'message' => 'Kanál nájdený, budú sa sťahovať všetky jeho videá.',
                ];
            }

            $id = $source['playlist'];

            try {
                $api->playlistItems($id, null, 1);
            } catch (YoutubeApiException $e) {
                if ($e->is('playlistNotFound')) {
                    return ['ok' => false, 'message' => 'Playlist ' . $id . ' na YouTube neexistuje alebo je súkromný.'];
                }

                throw $e;
            }

            return [
                'ok' => true, 'type' => 'playlist', 'id' => $id,
                'url' => 'https://www.youtube.com/playlist?list=' . $id,
                'message' => 'Playlist nájdený, budú sa sťahovať videá z neho.',
            ];
        } catch (YoutubeApiException $e) {
            // Výnimka môže niesť URL s API kľúčom — formulár dostane len text.
            return ['ok' => false, 'message' => 'YouTube teraz neodpovedá, overiť sa to nedalo. Uložiť sa dá aj tak.'];
        }
    }
}
