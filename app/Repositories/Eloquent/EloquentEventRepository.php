<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use App\Event;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\EventRepository;


class EloquentEventRepository extends AbstractRepository implements EventRepository
{
    public function entity()
    {
        return Event::class;
    }



}