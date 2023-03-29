<?php

namespace App\Traits;




trait HasRoute
{
    protected function getClasses()
    {
        return strtolower(class_basename(__CLASS__));
    }

    // For the specific event and post
    public function routeShow()
    {
        return route($this->getClasses() . '.show', [$this->id, $this->slug]);
    }
    

    // Genarali routes
    public function getUrlAttribute()
    {
        return [
            'show'      =>  route($this->getClasses() . '.show', [$this->id, $this->slug]),
            'edit'      =>  route($this->getClasses() . '.edit', [$this->id]),
            'update'    =>  route($this->getClasses() . '.update', $this->id),
            'store'     =>  route($this->getClasses() . '.store'),
            'destroy'   =>  route($this->getClasses() . '.delete', $this->id),
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
