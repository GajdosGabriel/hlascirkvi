<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    protected $guarded = [];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getThumbImageUrlAttribute()
    {
        if(App::environment('local')) {
            return "https://hlascirkvi.sk/storage/{$this->thumb}";
        };

        return "storage/{$this->thumb}";
    }

    public function getOriginalImageUrlAttribute()
    {
        return "storage/{$this->url}";
    }

}
