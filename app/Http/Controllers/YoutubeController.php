<?php

namespace App\Http\Controllers;

use App\Models\Canal;
use App\Models\User;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\YoutubeApi;
use App\Services\Youtube\YoutubeApiException;

class YoutubeController extends Controller
{
    public function __construct(private YoutubeApi $api)
    {
        $this->middleware('auth');
    }

    // Všetky funkcie v tomto controllery obsluhujú ručný prieskum alebo hľadanie.
    // Automatické hľadanie je v console

    public function searchUserVideo(User $user)
    {
        return view('users.search-new-video', ['user' => $user]);
    }

    public function searchCanalVideo(Canal $canal)
    {
        return view('users.search-new-video', ['user' => $canal]);
    }

    // Search by name in title and save/
    public function searchAndSaveUser(User $user, $slug)
    {
        return $this->searchAndSave($user, $user->canals()->first());
    }

    // Search by name in title and save/
    public function searchAndSaveCanal(Canal $canal, $slug)
    {
        return $this->searchAndSave($canal, $canal);
    }

    /**
     * Najnovšie videá konkrétneho kanála. Uploads playlist stojí jednu jednotku
     * kvóty, search.list, ktorý tu bol predtým, sto.
     */
    public function getNewVideoByChannel(User $user, $channelId)
    {
        if (! ChannelId::isId($channelId)) {
            return $this->fail('Neplatné ID kanála YouTube.');
        }

        try {
            $items = $this->api->playlistItems(YoutubeApi::uploadsPlaylistId($channelId))->items;
            $this->saveFoundVideos($user, array_map([VideoId::class, 'from'], $items));
        } catch (YoutubeApiException $e) {
            return $this->fail('YouTube API zlyhalo: ' . $e->getMessage());
        }

        return redirect('/');
    }

    // Z linku na Youtube vyhľadávanie zoberie základné informácie
    public function getVideoById($videoId)
    {
        if (! VideoId::isId($videoId)) {
            return response()->json(['error' => 'Neplatné ID videa.'], 422);
        }

        try {
            return response()->json($this->api->videos([$videoId])[$videoId] ?? false);
        } catch (YoutubeApiException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * Kanál s vlastným YouTube kanálom sa číta cez uploads playlist (1 jednotka,
     * len jeho videá). Fulltext search (100 jednotiek, cudzie videá) ostáva
     * len pre tých, ktorí kanál nemajú.
     */
    private function searchAndSave(Canal|User $owner, ?Canal $canal)
    {
        try {
            $channelId = $canal ? ChannelId::fromInput((string) $canal->youtube_channel) : null;

            $ids = $channelId !== null
                ? array_map([VideoId::class, 'from'], $this->api->playlistItems(YoutubeApi::uploadsPlaylistId($channelId))->items)
                : $this->searchVideosByUserName($owner);

            $this->saveFoundVideos($owner, $ids);
        } catch (YoutubeApiException $e) {
            return $this->fail('YouTube API zlyhalo: ' . $e->getMessage());
        }

        return redirect('/');
    }

    private function fail(string $message)
    {
        session()->flash('flash', $message);

        return redirect('/');
    }

    /**
     * @return string[] ID nájdených videí
     */
    public function searchVideosByUserName($canal): array
    {
        return array_map([VideoId::class, 'from'], $this->api->searchVideos($canal->title, 30));
    }

    /**
     * Príspevky patria kanálu — pri používateľovi sa uložia jeho kanálu, ak
     * nejaký spravuje.
     */
    private function saveFoundVideos(Canal|User $owner, array $ids): void
    {
        $canal = $owner instanceof Canal ? $owner : $owner->canals()->first();
        $ids = array_filter($ids);

        if ($ids === [] || $canal === null) {
            session()->flash('flash', 'Nenašli sa žiadne videá!');

            return;
        }

        $saved = (new VideoImporter($this->api))->import($canal, $ids);

        session()->flash('flash', 'Nových videí: ' . count($saved));
    }
}
