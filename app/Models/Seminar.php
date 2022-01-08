<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seminar extends Model
{
    use  SoftDeletes, HasFactory;
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
