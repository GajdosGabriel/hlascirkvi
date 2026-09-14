<?php

namespace App\Http\Controllers;

use App\Models\Canal;
use App\Models\User;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\YoutubeApi;

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

    public function searchOrganizationVideo(Canal $organization)
    {
        return view('users.search-new-video', ['user' => $organization]);
    }

    // Search by name in title and save/
    public function searchAndSaveUser(User $user, $slug)
    {
        $this->saveFoundVideos($user, $this->searchVideosByUserName($user));

        return redirect('/');
    }

    // Search by name in title and save/
    public function searchAndSaveOrganization(Canal $organization, $slug)
    {
        $this->saveFoundVideos($organization, $this->searchVideosByUserName($organization));

        return redirect('/');
    }

    /**
     * Najnovšie videá konkrétneho kanála. Uploads playlist stojí jednu jednotku
     * kvóty, search.list, ktorý tu bol predtým, sto.
     */
    public function getNewVideoByChannel(User $user, $channelId)
    {
        $items = $this->api->playlistItems(YoutubeApi::uploadsPlaylistId($channelId))->items;

        $this->saveFoundVideos($user, array_map([VideoId::class, 'from'], $items));

        return redirect('/');
    }

    // Z linku na Youtube vyhľadávanie zoberie základné informácie
    public function getVideoById($videoId)
    {
        return response()->json($this->api->videos([$videoId])[$videoId] ?? false);
    }

    /**
     * @return string[] ID nájdených videí
     */
    public function searchVideosByUserName($organization): array
    {
        return array_map([VideoId::class, 'from'], $this->api->searchVideos($organization->title, 30));
    }

    /**
     * Príspevky patria kanálu — pri používateľovi sa uložia jeho kanálu, ak
     * nejaký spravuje.
     */
    private function saveFoundVideos(Canal|User $owner, array $ids): void
    {
        $canal = $owner instanceof Canal ? $owner : $owner->organizations()->first();
        $ids = array_filter($ids);

        if ($ids === [] || $canal === null) {
            session()->flash('flash', 'Nenašli sa žiadne videá!');

            return;
        }

        $saved = (new VideoImporter($this->api))->import($canal, $ids);

        session()->flash('flash', 'Nových videí: ' . count($saved));
    }
}
