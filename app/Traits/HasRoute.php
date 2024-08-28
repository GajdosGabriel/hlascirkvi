<?php

namespace App\Traits;

use App\Enums\PageType;

trait HasRoute
{
    protected function getClasses()
    {
        return strtolower(class_basename(__CLASS__));
    }


    // Genarali routes
    public function getUrlAttribute()
    {
        return [
            'index'     => route(typePage() . '.' . $this->getClasses() . '.index'),
            'show'      => route(typePage() . '.' . $this->getClasses() . '.show', $this->id),
            'edit'      => route(typePage() . '.' . $this->getClasses() . '.edit', $this->id),
            'update'    => route(typePage() . '.' . $this->getClasses() . '.update', $this->id),
            'store'     => route(typePage() . '.' . $this->getClasses() . '.store'),
            'destroy'   => route(typePage() . '.' . $this->getClasses() . '.destroy', $this->id),
        ];
    }

    // public function getUrlAttribute()
    // {
    //    return [
    //         'show'      =>  route($this->getClasses(). '.show', [ $this->id, $this->slug]),
    //         'edit'      =>  route($this->getClasses(). '.edit', [ $this->id]),
    //         'update'    =>  route($this->getClasses(). '.update', $this->id),
    //         'store'     =>  route($this->getClasses(). '.store'),
    //         'destroy'   =>  route($this->getClasses(). '.delete', $this->id),
    //    ];
    // }


}
