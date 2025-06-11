<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seminar extends Model
{
    use  SoftDeletes, HasFactory, HasOrganization;
    protected $guarded = ['title' => \App\Casts\StringLength255::class];
    // public $timestamps = false;

    protected $with = ['organization'];


    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
