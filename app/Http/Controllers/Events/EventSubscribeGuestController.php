<?php

namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventSubscribeGuestForm;
use App\Notifications\Subscribe\NewSubscribe;
use Illuminate\Support\Facades\Notification;

class EventSubscribeGuestController extends Controller
{

    // Prichádzalo mnoho chybových hlásení
    public function index()
    {
        abort(404);
        return back();
    }
    
    // Neregistrovaný user. Nového usera najprv zaregistruje, potom prihlási a nakoniec odhlási.
    public function store(EventSubscribeGuestForm $request, Event $event)
    {

        if ($user = User::whereEmail($request->email)->first()) {
            \Auth::login($user, true);
        } else {
            $user = new User([
                'first_name' =>  $request->first_name,
                'last_name' =>  $request->last_name,
                'email' =>  $request->email,
                'password' => bcrypt('registracnyformularheslo'),
            ]);

            $user->save();

            \Auth::login($user, true);
        }

        $subscribe = $event->subscribe();

        Notification::send([$subscribe->organization->user, $event->organization->user], new NewSubscribe($subscribe));

        return redirect('akcie/' . $event->id . '/' . $event->slug);
    }
}
