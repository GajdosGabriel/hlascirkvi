<?php

namespace App\Traits;




trait HasRoute
{
    protected function getClasses()
    {
        return strtolower(class_basename(__CLASS__));
    }

    // Pre modely s vlastnou routou {model}.show
    public function routeShow()
    {
        return route($this->getClasses() . '.show', [$this->id, $this->slug]);
    }
    

    /**
     * Adresa detailu. Vracalo sa tu pole odkazov na routy post.edit, post.update,
     * post.store a post.delete — ani jedna z nich neexistuje (CRUD príspevkov
     * beží pod menami profile.posts.* a admin.post.*), takže každý
     * prístup k $post->url skončil RouteNotFoundException a zhodil serializáciu
     * príspevku. Šablóny aj resources/js/posts/card/card.vue pritom čakajú
     * obyčajný reťazec.
     */
    public function getUrlAttribute()
    {
        return $this->routeShow();
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
