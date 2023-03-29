<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use Carbon\Carbon;
use App\Models\Prayer;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\PrayerRepository;


class EloquentPrayerRepository extends AbstractRepository implements PrayerRepository
{
    public function entity()
    {
        return Prayer::class;
    }

    /*
     *  Prayers wrote by users
     */
    public function prayersWroteByUsers()
    {
        return  $this->entity->whereNotIn('organization_id', [100, 648, 649, 650]);
    }

// 518 prayer bola zlá

    public function unansweredPrayers()
    {
        return $this->prayersWroteByUsers()->whereNull('fulfilled_at');
    }
}
