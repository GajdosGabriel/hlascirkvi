<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 12. 12. 2019
 * Time: 10:02
 */

namespace App\Services\Extractor;

use Carbon\Carbon;
use App\Repositories\Eloquent\EloquentPostRepository;

class ExtractYoutubeComment
{

    public function handle()
    {
        $this->getComments();
    }

    // Posts ktoré nemajú video_duration ešte neboli skenované na commentáre. Boli však skontrolované 
    // na dostupnosť, embeddable(prehrávanie iba na YT) a či video existuje video_ available. Takto prejdeme všetky videa.

    public function getPosts()
    {
        return (new EloquentPostRepository)->postsByUpdater(15)->where('video_id', '<>', null)
            // ->where('created_at', '<=', Carbon::now()->subWeek(2))
            ->whereVideoDuration(null)
            ->whereVideoAvailable(null)
            ->take(1) // Počet príspevkov kontroly commentárov. Pôvodne jednorázovo bolo 10.
            ->latest()->get();
    }

    public function getComments()
    {

        // $comments = \Youtube::getCommentThreadsByVideoId('4PV31jcskz8');
        // $comments = \Youtube::getVideoInfo('4YoFSGc1ek0');

        // dd($comments);

        foreach ($this->getPosts() as $post) {

            // Chceck if video is commentAble
            $video = \Youtube::getVideoInfo($post->video_id);

            // If video dont exist any more
            if (!$video) {
                $post->update(['video_available' => $video]);
                continue;
            };

            // Put video duration
            if (!$post->duration) {
                $post->update(['video_duration' => $video->contentDetails->duration]);
            };

            // If video is dont available // is private
            if (!$video->status->embeddable) {
                $post->update(['video_available' => false]);
                continue;
            };


            // If video has enable comments
            if (!isset($video->statistics->commentCount)) {
                continue;
            };

            // If video doest have any comments
            if (!$video->statistics->commentCount > 0) {
                continue;
            };


            $comments = \Youtube::getCommentThreadsByVideoId($post->video_id);
            // $video = \Youtube::getVideoInfo('rie-hPVJ7Sw');


            foreach ($comments as $comment) {
                $bodyComment = $comment->snippet->topLevelComment->snippet->textDisplay;
                if (strlen($bodyComment) < 10) {
                    continue;
                }
                // Check if spam url links
                if (preg_match('/(http|www|mailto)/', $bodyComment)) {
                    continue;
                }
                // Check duplicity of comments
                if ($post->comments()->whereBody($bodyComment)->exists()) {
                    continue;
                }

                $post->comments()->create([
                    'user_id' => 100,
                    'body' => cleanHardSpace($comment->snippet->topLevelComment->snippet->textDisplay),
                    'user_avatar' => $comment->snippet->topLevelComment->snippet->authorProfileImageUrl,
                    'user_name' => $comment->snippet->topLevelComment->snippet->authorDisplayName,
                    // 'created_at' => \Carbon\Carbon::parse($comment->snippet->topLevelComment->snippet->publishedAt)->format('Y-m-d h:i:s'),
                ]);
            }
        }
    }
}
