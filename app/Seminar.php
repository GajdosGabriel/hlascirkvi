<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seminar extends Model
{
    use  SoftDeletes;
    protected $guarded = [];
    // public $timestamps = false;

    protected $with = ['organization'];


    public function posts() {
        return $this->belongsToMany(Post::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }


}
