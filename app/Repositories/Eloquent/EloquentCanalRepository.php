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

    /**
     * Kanály, ktoré sa dnes majú hľadať na YouTube podľa mena. Deň nesie
     * stĺpec `organizations.import_day` s rovnakým číslovaním ako
     * Carbon::dayOfWeek — predtým to bol jeden zo siedmich updaterov typu
     * `dayOfWeek` a výber cez sedem vetiev s natvrdo zapísanými slugmi.
     */
    public function getUsersByDayOfWeek()
    {
        return $this->getResult(Carbon::now()->dayOfWeek);
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
    protected function getResult(int $day)
    {
        return $this->entity->where('import_day', $day)->where(function ($query) {
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
}
