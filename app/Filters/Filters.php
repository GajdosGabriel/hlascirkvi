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

        foreach($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder->latest();
    }

    public function getFilters()
    {
        return array_filter($this->request->only($this->filters));
    }


}