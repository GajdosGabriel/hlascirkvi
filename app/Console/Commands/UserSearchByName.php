<?php

namespace App\Console\Commands;

use App\Jobs\DownloaderYoutube;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Services\ImageResize;
use Illuminate\Console\Command;


class UserSearchByName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSearchByName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search video by user name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $organizations = (new EloquentOrganizationRepository())->getUsersByDayOfWeek();

        foreach($organizations as $organization) {
            // Set default parameters
            $params = [
                'q'             => $organization->title,
                'type'          => 'video',
                'part'          => 'id, snippet',
                'maxResults'    => 30
            ];

            $videoList = \Youtube::searchAdvanced($params);

            foreach ($videoList as $video) {
                if(! \DB::table('posts')->whereVideoId($video->id->videoId)->exists())
                {
                    $post = $organization->posts()->create([
                        'title' => $video->snippet->title,
                        'video_id' => $video->id->videoId,
                        'body' => $video->snippet->description
                    ]);

                    (new ImageResize())->resizeImage($post, $video->snippet->thumbnails->medium->url );

//                    DownloaderYoutube::dispatch($video->id->videoId)
//                        ->delay(now()->addMinutes(10));
                }
            }

        }
    }



}
