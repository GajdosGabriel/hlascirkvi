<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 29.01.2019
 * Time: 9:34
 */

namespace App\Repositories\Contracts;


interface CanalRepository
{
    public function getYoutubeVideos();
    public function createPost($organizationId, array $properties );

}