<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use Carbon\Carbon;
use App\Models\Event;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\EventRepository;


class EloquentEventRepository extends AbstractRepository implements EventRepository
{
    public function entity()
    {
        return Event::class;
    }

    /*
     *  Only if organization ispublished // aside modul and public events
     */
    public function organizationPublished()
    {
        return $this->entity->whereHas('organization', function (Builder $query) {
            $query->wherePublished(1);
        })->wherePublished(1);
    }

    /*
     *  Najskôr začinajúce eventy // aside modul
     */
    public function orderByStarting()
    {
        return $this->organizationPublished()->where('start_at', '>', Carbon::now())->orderBy('start_at', 'asc');
    }

    /*
     *  Práve prebiehajúce eventy // akcie
     */
    public function curentlyEvents()
    {
        return $this->organizationPublished()->where('start_at', '<=', Carbon::now())
            ->where('end_at', '>=', Carbon::now())
            ->orderBy('end_at', 'asc');
    }
}
