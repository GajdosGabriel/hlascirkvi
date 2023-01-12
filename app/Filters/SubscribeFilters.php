<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 13.09.2018
 * Time: 7:52
 */

namespace App\Filters;

use App\Models\Organization;

class SubscribeFilters extends Filters
{

    protected $filters = [
        'search',
        'deletedAt'
    ];


    public function search()
    {
        session()->flash('search', $this->request->search);

        $organizations = Organization::where('title', 'LIKE', '%' . $this->request->search . '%')->pluck('id');

        return $this->builder->whereIn('organization_id', $organizations);
    }

    public function deletedAt()
    {
         return $this->builder->onlyTrashed();
    }



}
