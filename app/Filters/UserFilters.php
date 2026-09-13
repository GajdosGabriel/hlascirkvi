<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;

use App\Enums\ModelStatus;

class UserFilters extends Filters
{
    protected $filters = ['search', 'banned', 'deletedAt'];

    public function search()
    {
        session()->flash('search', $this->request->search);

        return $this->builder
            ->where('email', 'LIKE', $this->likePattern($this->request->search))
            ->orWhere('first_name', 'LIKE', $this->likePattern($this->request->search))
            ->orWhere('last_name', 'LIKE', $this->likePattern($this->request->search))
            ->orWhere('email', 'LIKE', $this->likePattern($this->request->search));
    }

    public function banned()
    {
        return $this->builder->where(function ($query) {
            $query->where('status', ModelStatus::Blocked->value)->orWhere('disabled', true);
        });
    }

    public function deletedAt()
    {
        return $this->builder->onlyTrashed();
    }
}
