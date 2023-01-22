<?php

namespace App\Services\VisitModels;

class Counter
{

    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $this->isValid();
    }

    public function isValid()
    {
        if ($this->userIdentity()) {
            $this->makeIncrement();
        }
    }

    public function userIdentity()
    {
        if (auth()->user()) {
            return auth()->user()->org_id != $this->model->organization_id;
        }
        return true;
    }


    public function makeIncrement()
    {
        $this->model->increment('count_view');
    }
}
