<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prayer extends Model
{
    use Notifiable,  SoftDeletes;

    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
