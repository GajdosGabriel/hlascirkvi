<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use App\Models\Event;
use Carbon\Carbon;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\EventRepository;


class EloquentEventRepository extends AbstractRepository implements EventRepository
{
    public function entity()
    {
        return Event::class;
    }

       /*
     *  Najskôr začinajúce eventy // aside modul
     */
    public function orderByStarting()
    {
        return $this->entity->where('start_at', '>', Carbon::now())->wherePublished(1)->orderBy('start_at', 'asc');

    }



}
