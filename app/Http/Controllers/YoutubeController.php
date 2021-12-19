<?php

namespace App\Http\Controllers;


use App\Models\Organization;
use App\Services\ImageResize;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Events\Posts\BufferPublisherVideo;

class YoutubeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Všetky funkcie v tomto controllery obsluhujú ručný prieskum alebo hľadanie.
    // Automatické hľadanie je v console

    public function searchUserVideo(User $user) {
        return view('users.search-new-video', ['user' =>$user]);
    }

    public function searchOrganizationVideo(Organization $organization) {
        return view('users.search-new-video', ['user' =>$organization]);
    }

    // Search by name in title and save/
    public function searchAndSaveUser(User $user, $slug)
    {
        $videoList = $this->searchVideosByUserName($user);
        $this->saveFindedVideo($user, $videoList);
        return redirect('/');
    }


    // Search by name in title and save/
    public function searchAndSaveOrganization(Organization $organization, $slug)
    {
        $videoList = $this->searchVideosByUserName($organization);
        $this->saveFindedVideo($organization, $videoList);
        return redirect('/');
    }


  // vyhľadávanie cez konkretny kanál max.50 results
    public function getNewVideoByChannel(User $user, $channelId)
    {
        $videoList = \Youtube::listChannelVideos($channelId);

        $this->saveFindedVideo($user, $videoList);

        return redirect('/');
    }


    public function searchVideosByUserName($organization) {
        // Set default parameters
        $params = [
            'q'             => $organization->title,
            'type'          => 'video',
            'part'          => 'id, snippet',
            'maxResults'    => 30
        ];

      return $videoList  = \Youtube::searchAdvanced($params);
    }


    // Search and save by prieskum daily new user videos.
    /**
     * @param $user
     * @param $videoList
     */
    private function saveFindedVideo($organization, $videoList)
    {
        // check if any videos exists
        if(empty($videoList)) {

            session()->flash('flash', 'Nenašli sa žiadne videa!');
            return;
        }

        foreach ($videoList as $video) {
            if(! \DB::table('posts')->whereVideoId($video->id->videoId)->exists())
            {
                $post = $organization->posts()->create([
                    'title' => $video->snippet->title,
                    'video_id' => $video->id->videoId,
                    'body' => $video->snippet->description,
                    'category_id' => 2,
                    'published' => 0
                ]);

                $image = new ImageResize();
                $image->resizeImage($post, $video->snippet->thumbnails->medium->url );

            }
        }

    }


}
