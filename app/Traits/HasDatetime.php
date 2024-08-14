<?php

namespace App\Traits;

use App\Models\BigThink;


trait HasDatetime
{

    public function getDateAttribute()
    {
        return date('d m Y', strtotime($this->created_at));
    }

    public function getDatetimeAttribute()
    {
        return date('d m Y', strtotime($this->created_at));
    }

    public function getDateForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    
}