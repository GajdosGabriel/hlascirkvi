<?php

namespace App\Http\Controllers\Events;

use App\Role;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventSubscribe;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventSubscribeForm;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Subscribe\NewSubscribe;


class EventSubscribeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('subscribeByForm');
    }

    public function index(Event $event)
    {
        $this->authorize('update', $event);
        return view('profiles.events.users.index', compact('event'));
    }

    public function update(Event $event, EventSubscribe $subscribe, Request $request)
    {
        $subscribe->update($request->all());
        return back();
    }

    public function store(Event $event, Request $request)
    {
        $subscribe =  $event->subscribe();

        Notification::send( [$subscribe->organization->user, $event->organization->user] , new NewSubscribe($subscribe));

        return back();
    }

    public function destroy(Event $event, EventSubscribe $subscribe)
    {
        $subscribe->delete();
        return back();
    }


    // Nového usera najprv zaregistruje, potom prihlási a nakoniec odhlási.
    public function subscribeByForm(EventSubscribeForm $request, Event $event)
    {

        \Auth::logout(auth()->user());

        $count = count($request->input('first_name'));

        for ($i = 0; $i < $count; ++$i) {

            if ($user = User::whereEmail($request->email[$i])->first()) {
                \Auth::login($user, true);
            } else {
                $user = new User([
                    'first_name' =>  $request->first_name[$i],
                    'last_name' =>  $request->last_name[$i],
                    'slug' =>  $request->first_name[$i] . '-' . $request->last_name[$i],
                    'email' =>  $request->email[$i],
                    'password' => bcrypt('registracnyformularheslo'),
                ]);

                $user->save();

                \Auth::login($user, true);
            }

            $event->subscribe();
            \Auth::logout(auth()->user());
        }

        return redirect()->route('akcie.show', [$event->id, $event->slug]);
    }
}
