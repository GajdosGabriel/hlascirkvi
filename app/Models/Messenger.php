<?php

namespace App\Models;

use App\Traits\HasCanal;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Messenger extends Model
{
    use Notifiable, HasCanal;

    protected $guarded =[];


    public function senderUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestedUser()
    {
        return $this->belongsTo(User::class, 'requested_user');
    }

}
