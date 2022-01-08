<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use  SoftDeletes, HasFactory;
    protected $guarded = [];
    // public $timestamps = false;

    public function posts() {
        return $this->belongsToMany(Post::class);
    }



}
