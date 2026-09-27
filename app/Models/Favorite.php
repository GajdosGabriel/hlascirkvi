<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded = [];
    protected $hidden = ['pending_user_id'];

    public function favorited()
    {
        return $this->morphTo();
    }


}
