<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 22.09.2018
 * Time: 7:35
 */

namespace App\Filters;


use Illuminate\Http\Request;

abstract class Filters
{

    protected $request, $builder;
    protected $filters = [];


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function apply($builder)
    {
        $this->builder = $builder;


        // if(! $this->request->only('posts')) return $this->builder->latest();


            foreach($this->filters as $filter)
        {
            if(method_exists($this, $filter) && $this->request->posts == $filter)
            {
                $this->$filter($this->request->$filter);
            }
        }


        return $this->builder->latest();

    }


}