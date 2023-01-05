<?php

namespace App\Traits;



trait HasFormaten
{

    public function dateForHumans()
    {
        return $this->created_at->diffForHumans();
    }


   
    
}