<?php

namespace App;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Messenger extends Model
{
    use Notifiable;

    protected $guarded =[];


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestedUser()
    {
        return $this->belongsTo(User::class, 'requested_user');
    }

    public function scopeUserMessages($user)
    {
        if(auth()->check()) {
            $first = $this->whereUserId(auth()->user()->id)->whereRequestedUser($user)->get();
            $second = $this->whereUserId($user)->whereRequestedUser(auth()->user()->id)->get();

//         spojit dve kolekcie do jednej, zoradit a vratit vysledok
          return $first->concat($second)->sortBy('created_at');
        }

    }



}
