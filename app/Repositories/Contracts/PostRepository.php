<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:17
 */

namespace App\Repositories\Contracts;


interface PostRepository extends InterfaceRepository
{
    public function getPostsByUpdater($idUpdaters, $perOrganization = 5);
    public function countUnwatchedSundayServicesVideos();
    public function findAndPublishPost($post, $IdUpdater);

    // Buffer publisher (App\Services\Buffer)
    public function countWaitingPosts();
    public function countWaitingPostsSince($since);
    public function waitingOrganizations($idUpdater, $freshSince = null);
    public function nextWaitingPost($organizationId, $freshSince = null);
    public function publishPost($post, $IdUpdater, $publishedAt = null);
}
