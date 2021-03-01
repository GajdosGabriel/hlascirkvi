<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 24.08.2018
 * Time: 7:51
 */

namespace App\Services;

use App\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Carbon\Carbon;


class Buffer
{

    protected $post;

    public function __construct()
    {
        $this->post = new EloquentPostRepository();
    }

    public function handler()
    {
        /*
        * Umožní publikovať denne max 2 videa na predný zoznam,
        * do zoznamu započíta aj živé prenosy ostatné zaradí do buffer
        */
//        if($this->post->getPostsByDatetime()->count() >= 2) return;


        /*
        * Spočíta userov v buffer ktorí čakajú na zverenenie ale -1
        */
        $countOfUsers = $this->post->getUnpublishedPosts()->groupBy('organization_id')->count() -1;


        $usersId = $this->idLastPublishedOrganizations($countOfUsers);

//        dd($usersId);
        $this->getBufferPost($usersId);
    }

    /*
     * Vyberie posledných zverejnených userov ale -1
     */
    public function idLastPublishedOrganizations($countOfUsers)
    {
      return  $this->post->postsByUpdater(15)
       ->latest()->take($countOfUsers)->get()->pluck('organization_id');
    }

    /*
     * Find latest post in buffer
     */
    public function getBufferPost($usersId)
    {
       $post = $this->post->getPostForPublish($usersId);

       $this->post->publishPost($post, $idUpdater = 15);

        if(empty($post)) {
            $this->ifBufferIsEmpty();
            return;
        }
    }

    public function ifBufferIsEmpty()
    {
        ( new EloquentUserRepository())->usersHasRoleAdmin();
    }




}
