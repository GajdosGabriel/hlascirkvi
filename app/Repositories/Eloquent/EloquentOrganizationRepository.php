<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 29.01.2019
 * Time: 9:36
 */

namespace App\Repositories\Eloquent;


use App\Organization;
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

        if($dayNumber == 0) return $this->getResult('nedela');
        if($dayNumber == 1) return $this->getResult('pondelok');
        if($dayNumber == 2) return $this->getResult('utorok');
        if($dayNumber == 3) return $this->getResult('streda');
        if($dayNumber == 4) return $this->getResult('stvrtok');
        if($dayNumber == 5) return $this->getResult('piatok');
        if($dayNumber == 6) return $this->getResult('sobota');

    }

    protected function getResult($slug) {
      return $this->entity->whereHas('updaters', function($query) use($slug) {
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
        return $this->entity->whereHas('users', function ($query) use($idUser) {
            $query->whereId($idUser);
        })->get();
    }

    public function createPost($organizationId, array $properties )
    {
        return  $this->find($organizationId)->posts()->create($properties);
    }




}