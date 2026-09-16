<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:17
 */

namespace App\Repositories\Contracts;


use App\Enums\PostSection;

interface PostRepository extends InterfaceRepository
{
    public function postsInSection(PostSection $section);
    public function groupedBySection(PostSection $section, $perCanal = 5);
    public function countUnwatchedSundayServicesVideos();
    public function findAndPublishPost($postId);

    // Buffer publisher (App\Services\Buffer)
    public function countWaitingPosts();
    public function countWaitingPostsSince($since);
    public function waitingCanals($freshSince = null);
    public function nextWaitingPost($canalId, $freshSince = null);
    public function publishPost($post, $publishedAt = null);
}
