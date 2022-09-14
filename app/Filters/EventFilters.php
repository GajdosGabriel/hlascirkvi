<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;


use Carbon\Carbon;
use App\Models\Event;
use App\Models\Village;
use Illuminate\Http\Request;

class EventFilters extends Filters
{
    protected $filters = ['location', 'search', 'finished', 'unpublished', 'organization', 'deletedAt'];

    public function location($value)
    {
        return $this->builder->where('end_at', '>', Carbon::now())->whereIn(
            'village_id',
            Village::whereDistrictId($value)->get()->pluck('id')
        );
    }

    public function finished($value)
    {
        return $this->builder->where('end_at', '<', Carbon::now())->orderBy('start_at', 'desc');
    }

    public function unpublished($value)
    {
       return $this->builder->where('published', 0);
    }

    public function organization($value)
    {
       return $this->builder->where('organization_id', $value);
    }

    public function deletedAt()
    {
         return $this->builder->onlyTrashed();
    }

    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder
            ->where('title', 'LIKE', '%' . $this->request->search . '%')
            // ->orWhere('city', 'LIKE', '%' . $this->request->title . '%')
        ;
    }
}
