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
    protected $filters = ['search', 'banned', 'deletedAt'];

    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder
            ->where('email', 'LIKE', '%' . $this->request->search . '%')
            ->orWhere('first_name', 'LIKE', '%' . $this->request->search . '%')
            ->orWhere('last_name', 'LIKE', '%' . $this->request->search . '%')
            ->orWhere('email', 'LIKE', '%' . $this->request->search . '%');
    }

    public function banned(){
        return $this->builder->whereDisabled(1);
    }

    public function deletedAt()
    {
         return $this->builder->onlyTrashed();
    }
}
