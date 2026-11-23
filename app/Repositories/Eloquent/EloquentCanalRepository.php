<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 29.01.2019
 * Time: 9:36
 */

namespace App\Repositories\Eloquent;


use App\Models\Canal;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\CanalRepository;

class EloquentCanalRepository extends AbstractRepository implements CanalRepository
{
    public function entity()
    {
        return Canal::class;
    }

    /**
     * Kanály, ktorým sa sťahujú videá. `youtube_disabled_at` drží tie, ktorých
     * zdroj na YouTube už neexistuje — bez toho sa tá istá chyba 403 opakovala
     * v každom dennom behu (App\Services\Youtube\DisableImport).
     */
    public function getYoutubeVideos()
    {
        return $this->entity
            ->whereNull('youtube_disabled_at')
            ->notPaused()
            ->where(function ($query) {
                $query->where('youtube_channel', '<>', "")
                    ->orWhere('youtube_playlist', '<>', "");
            })
            ->get();
    }



    public function usersCanals($idUser)
    {
        return $this->entity->whereHas('users', function ($query) use ($idUser) {
            $query->whereId($idUser);
        });
    }

    public function createPost($canalId, array $properties)
    {
        return  $this->find($canalId)->posts()->create($properties);
    }
}
