<?php

namespace App\Traits;




trait HasRoute
{
    protected function getClasses()
    {
        return strtolower(class_basename(__CLASS__));
    }



    public function routeShow()
    {
        return route($this->getClasses(). '.show', [$this->id, $this->slug]);
    }

    
}