<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;

use App\Post;
use Carbon\Carbon;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\PostRepository;
use CyrildeWit\EloquentViewable\Support\Period;


class EloquentPostRepository extends AbstractRepository implements PostRepository
{

    public function entity()
    {
        return Post::class;
    }


    public function getPostsByUpdater($idUpdaters) {
        return $this->postsByUpdater($idUpdaters)
            ->orderBy('id', 'desc')->get()->groupBy('organization_id');
    }


    public function postsByUpdater($idUpdaters) {
        return $this->entity->whereHas('updaters', function ($query) use ($idUpdaters) {
            $query->whereId($idUpdaters);
        });
    }

    public function postsByTag($idTag) {
        return $this->entity->whereHas('tags', function ($query) use ($idTag) {
            $query->whereId($idTag);
        });
    }


    protected function unpublished() {
        return $this->entity->withoutGlobalScope('published')->doesntHave('updaters');
    }

    public function unpublishedPaginate($perPage) {
        return $this->unpublished()->paginate($perPage);
    }

    // For buffer ---------------

    public function getUnpublishedPosts() {
        return $this->unpublished()->get();
    }

    public function getPostForPublish($usersId) {
      return $this->unpublished()
            ->whereNotIn('organization_id',  $usersId)
            ->latest()->orderBy('id', 'desc')->first();
    }

    /*
     * Publishing manually For adminController
     */
    public function findAndPublishPost($post, $IdUpdater)
    {
        $post = $this->entity->withoutGlobalScope('published')->find($post);
        $post->update(['created_at' => now()]);
        $post->updaters()->attach($IdUpdater);
    }

    /*
     * Published for buffer
     */
     public function publishPost($post, $IdUpdater)
    {
        $post->update(['created_at' => now()]);
        $post->updaters()->attach($IdUpdater);
    }


    public function getPostsByDatetime()
    {
       return $this->entity
            ->whereYear('created_at',   '=', date('Y') )
            ->whereMonth('created_at',  '=', date('m'))
            ->whereDay('created_at',    '=', date('d'))
            ->get();
    }

    // For buffer ---------------



    public function countUnwatchedSundayServicesVideos()
    {
        $unwatchedVideos = $this->entity->whereHas('organization.updaters', function ($query) {
            $query->whereId(1);
        })->where( 'created_at', '>', session()->get('lastVisit'))->count();

        if($unwatchedVideos > 0) {
          session()->put('countUnwatchedVideos', $unwatchedVideos);
        }
    }

    public function postsBelongToOrganization($organizationId)
    {
        return $this->entity->whereOrganizationId($organizationId)->latest()->paginate(20);
    }



    /*
     *  Najsledovanejšie videa
     */
    public function newlleterMostVisited()
    {
        return $this->entity->where('created_at','>', Carbon::now()->subDays(30))
        ->orderByViews('desc', Period::pastDays(30));

    }




}
