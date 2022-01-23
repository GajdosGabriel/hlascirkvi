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

class OrganizationFilters extends Filters
{
    protected $filters = ['search'];

    public function search()
    {
        session()->flash('search', $this->request->title);
        return $this->builder->where('title', 'LIKE', '%' . $this->request->title . '%');
    }
}
