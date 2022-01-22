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

class UserFilters extends Filters
{
    protected $filters = ['search'];

    public function search()
    {
        return $this->builder->where('email', 'LIKE', '%' . $this->request->title . '%');
    }
}
