<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;


use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrayerFilters extends Filters
{
    protected $filters = ['search', 'fulfilled'];

    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder->where('body', 'LIKE', '%' . $this->request->search . '%');
    }

    public function fulfilled()
    {
         return $this->builder->whereNotNull('fulfilled_at');
    }

}
