<?php

namespace App\Http\Controllers\Events;

use App\Role;

use App\Models\User;
use App\Models\Event;
use App\Models\EventSubscribe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventSubscribeForm;
use App\Repositories\Contracts\UserRepository;

class EventSubscribesController extends Controller
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

    public function update(Event $event, EventSubscribe $eventSubscribe, Request $request)
    {
        $eventSubscribe->update([
            'confirmed' => $request->input('confirmed')
        ]);
        return back();
    }

    public function store(Event $event, Request $request)
    {
        $event->subscribe();
        session()->flash('flash', 'Ste prihlásený na akciu!!');
        return back();
    }

    public function destroy(Event $event, EventSubscribe $eventSubscribe)
    {
        $eventSubscribe->delete();
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
