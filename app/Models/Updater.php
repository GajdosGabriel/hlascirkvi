<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Updater extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function organizations() {
        return $this->belongsToMany(Organization::class);
    }

    public function posts() {
        return $this->belongsToMany(Post::class);
    }
}
