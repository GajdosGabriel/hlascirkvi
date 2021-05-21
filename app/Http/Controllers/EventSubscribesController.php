<?php

namespace App\Http\Controllers;

use App\Event;

use App\EventSubscribe;
use App\Http\Requests\EventSubscribeForm;
use App\Repositories\Contracts\UserRepository;
use App\Role;
use App\User;
use Illuminate\Http\Request;

class EventSubscribesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('subscribeByForm');
    }


    // Nového usera najprv zaregistruje, potom prihlási a nakoniec odhlási.
    public function subscribeByForm(EventSubscribeForm $request, Event $event) {

        \Auth::logout(auth()->user());

        $count = count($request->input('first_name'));

        for($i = 0; $i < $count; ++$i){

            if($user = User::whereEmail($request->email[$i])->first() )
            {
                \Auth::login($user, true);
            }
            else {
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


    public function confirmedSubscribtion(EventSubscribe $eventSubscribe)
    {
        $eventSubscribe->update(['confirmed' => 1]);
        session()->flash('flash', 'Rezervácia je potvrdená!');
        return back();
    }


}
