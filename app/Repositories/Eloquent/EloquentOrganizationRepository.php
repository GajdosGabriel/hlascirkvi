<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 29.01.2019
 * Time: 9:36
 */

namespace App\Repositories\Eloquent;


use App\Models\Organization;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\OrganizationRepository;
use Carbon\Carbon;

class EloquentOrganizationRepository extends AbstractRepository implements OrganizationRepository
{
    public function entity()
    {
        return Organization::class;
    }

    public function getUsersByDayOfWeek()
    {
        $dayNumber = Carbon::parse('now')->dayOfWeek;

        if ($dayNumber == 0) return $this->getResult('nedela');
        if ($dayNumber == 1) return $this->getResult('pondelok');
        if ($dayNumber == 2) return $this->getResult('utorok');
        if ($dayNumber == 3) return $this->getResult('streda');
        if ($dayNumber == 4) return $this->getResult('stvrtok');
        if ($dayNumber == 5) return $this->getResult('piatok');
        if ($dayNumber == 6) return $this->getResult('sobota');
    }

    protected function getResult($slug)
    {
        return $this->entity->whereHas('updaters', function ($query) use ($slug) {
            $query->whereSlug($slug);
        })->get();
    }


    public function getYoutubeVideos()
    {
        return $this->entity
            ->where('youtube_channel', '<>', "")
            ->orWhere('youtube_playlist', '<>', "")
            ->get();
    }



    public function usersOrganizations($idUser)
    {
        return $this->entity->whereHas('users', function ($query) use ($idUser) {
            $query->whereId($idUser);
        });
    }

    public function createPost($organizationId, array $properties)
    {
        return  $this->find($organizationId)->posts()->create($properties);
    }


    public function frontOrganizationsList()
    {
        $postsCount = \DB::table('posts')
            ->select('organization_id', \DB::raw('count(*) as posts_count'))
            ->where('youtube_blocked', 0)
            ->groupBy('organization_id');


        return  \DB::table('organizations')
            ->select([
                'organizations.slug',
                'organizations.id',
                'organizations.title',
                'posts_count',
            ])
            ->join('organization_updater', function ($join) {
                $join->on('organizations.id', '=', 'organization_updater.organization_id')
                    ->where('updater_id', 14);
            })

            ->joinSub($postsCount, 'posts', function ($join) {
                $join->on('organizations.id', '=', 'posts.organization_id');
            });
    }
}
