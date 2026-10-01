<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'favorited_id', 'favorited_type'];
    protected $hidden = ['pending_user_id'];

    public function favorited()
    {
        return $this->morphTo();
    }


}
