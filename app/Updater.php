<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Updater extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function organizations() {
        return $this->belongsToMany(Organization::class);
    }

    public function posts() {
        return $this->belongsToMany(Post::class);
    }
}
