<?php

namespace App\Traits;

use App\Models\Organization;


trait HasOrganization
{

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    
}