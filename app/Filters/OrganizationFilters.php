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
    protected $filters = ['search', 'unpublished'];

    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder
            ->where('title', 'LIKE', '%' . $this->request->search . '%')
            // ->orWhere('city', 'LIKE', '%' . $this->request->search . '%')
            ;
    }

    public function unpublished()
    {
         return $this->builder->wherePublished(0);
    }


}
