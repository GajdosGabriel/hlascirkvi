<?php

namespace App\Traits;

use App\Models\Canal;


trait HasCanal
{

    public function organization()
    {
        return $this->belongsTo(Canal::class);
    }

    
}