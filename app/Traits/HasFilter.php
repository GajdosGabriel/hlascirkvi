<?php

namespace App\Traits;


trait HasFilter
{
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }
}
