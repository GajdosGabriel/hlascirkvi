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
use Carbon\Carbon;

class EloquentCanalRepository extends AbstractRepository implements CanalRepository
{
    public function entity()
    {
        return Canal::class;
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

    /**
     * Len organizácie bez kanála aj bez playlistu. Kanál s vypnutým sťahovaním
     * (`youtube_disabled_at`) nespracuje ani jedna z ciest — to je zmysel
     * vypnutia, nie opomenutie.
     *
     * Hľadanie podľa mena je fulltext naprieč celým YouTube: stojí sto jednotiek
     * kvóty (`activities.list` nad známym kanálom jednu) a nie je obmedzené na
     * kanál organizácie. Organizáciám s vyplneným kanálom tak ťahalo videá druhý
     * raz v ten istý deň a vedelo im priradiť aj cudzie video, ktoré len
     * obsahovalo ich názov (kanál 465 Komunita Blahoslavenstiev). Tie už denne
     * spracuje `UserSearchByChannelAndPlaylist`, takže podľa mena ostávajú len
     * osoby, ku ktorým žiadny kanál nepatrí.
     */
    protected function getResult($slug)
    {
        return $this->entity->whereHas('updaters', function ($query) use ($slug) {
            $query->whereSlug($slug);
        })->where(function ($query) {
            $query->whereNull('youtube_channel')->orWhere('youtube_channel', '=', '');
        })->where(function ($query) {
            $query->whereNull('youtube_playlist')->orWhere('youtube_playlist', '=', '');
        })->get();
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
            ->where(function ($query) {
                $query->where('youtube_channel', '<>', "")
                    ->orWhere('youtube_playlist', '<>', "");
            })
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
