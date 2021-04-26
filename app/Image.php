<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $guarded = [];



    public function fileable()
    {
        return $this->morphTo();
    }

    public function getThumbImageUrlAttribute()
    {
        return "storage/{$this->thumb}";
    }

    public function getOriginalImageUrlAttribute()
    {
        return "storage/{$this->url}";
    }

}
