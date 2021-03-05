<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prayer extends Model
{
    use Notifiable,  SoftDeletes, Favoritable;

    protected $guarded = ['id'];
    protected $appends = ['favoritesCount', 'isFavorited'];


    public function user() {
        return $this->belongsTo(User::class);
    }
}
