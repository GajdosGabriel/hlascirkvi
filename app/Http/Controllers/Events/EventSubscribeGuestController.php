<?php

namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventSubscribeForm;

class EventSubscribeGuestController extends Controller
{
     // Neregistrovaný user. Nového usera najprv zaregistruje, potom prihlási a nakoniec odhlási.
     public function store(EventSubscribeForm $request, Event $event)
     {
 
         if ($user = User::whereEmail($request->email)->first()) {
             \Auth::login($user, true);
         } else {
             $user = new User([
                 'first_name' =>  $request->first_name,
                 'last_name' =>  $request->last_name,
                 'slug' =>  $request->first_name . '-' . $request->last_name,
                 'email' =>  $request->email,
                 'password' => bcrypt('registracnyformularheslo'),
             ]);
 
             $user->save();
 
             \Auth::login($user, true);
         }
 
         $event->subscribe();
 
         return redirect('akcie/'. $event->id . '/'. $event->slug);
     }
 
}
