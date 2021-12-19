<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BigThink extends Model
{
    protected $guarded = [];


    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }
}
