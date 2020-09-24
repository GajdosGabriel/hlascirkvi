<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;


use App\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventFilters extends Filters
{

    public function getDistrict($district) {
        return $this->builder->where('end_at', '>', Carbon::now())->whereIn('village_id',
            Village::whereDistrictId($district)->get()->pluck('id'));
    }



    public function getUnpublished()
    {
        return $this->builder->wherePublished(0);
    }



    public function apply($builder)
    {
        $this->builder = $builder;


        // Events

        if($this->request->district) return $this->getDistrict($this->request->district);

        if($this->request->event) return $this->getUnpublished($this->request->event);

        return $this->builder->where('end_at', '>', Carbon::now())->wherePublished(1);

    }

}