<?php

namespace App\Traits;

use App\Models\Canal;


trait HasCanal
{

    public function canal()
    {
        return $this->belongsTo(Canal::class);
    }

    
}