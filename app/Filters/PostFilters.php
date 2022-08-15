<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 13.09.2018
 * Time: 7:52
 */

namespace App\Filters;


use Carbon\Carbon;
use CyrildeWit\EloquentViewable\Support\Period;

class PostFilters extends Filters
{

    protected $filters = ['mostVisited' , 
    'recomended' , 
    'first' , 
    'latestComments', 
    'trends', 
    'search', 
    'unpublished', 
    'deletedAt', 
    'videoAvailable'];


    public function recomended()
    {
        return $this->builder->has('favorites');
    }

    public function first()
    {
       return $this->builder->orderBy('id', 'asc');
    }

    public function mostVisited()
    {
        return $this->builder->orderBy('count_view', 'desc');
    }

    public function latestComments()
    {
        return $this->builder->has('comments');
    }

    public function unpublished()
    {
         return $this->builder->whereNull('published');
    }

    public function videoAvailable($value)
    {
        return $this->builder->where('video_available', 0);
    }

    public function deletedAt()
    {
         return $this->builder->onlyTrashed();
    }

    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder->where('title', 'LIKE', '%' . $this->request->search . '%');
    }


    // Najsledovanejšie za dva týždne zo všetkých
    public function trends()
    {
        return $this->builder->orderByViews('desc', Period::pastDays(14));
//        return $this->builder->orderByViews('desc', Period::pastDays(14));
        // Najsledovanejšie z 2-týždňových videí
//          return $this->builder->where('created_at','>', Carbon::now()->subDays(14))->orderByViews('desc');
    }





}