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
    public function getPostsByUpdater($idUpdaters);
    public function countUnwatchedSundayServicesVideos();
    public function findAndPublishPost($post, $IdUpdater);


}