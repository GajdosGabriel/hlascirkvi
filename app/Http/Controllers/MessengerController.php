<?php

namespace App\Http\Controllers;

use App\Models\Messenger;
use App\Models\Canal;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessengerRequest;
use App\Notifications\Messengers;
use App\Notifications\Canals\CanalMessage;

class MessengerController extends Controller
{

    /**
     * Správa pre kanál z jeho verejnej stránky. Kontakty kanála sa na stránke
     * nezobrazujú; ak kanál e-mail nemá, niet kam správu poslať.
     */
    public function toCanal(StoreMessengerRequest $request, Canal $organization) {

        abort_unless($organization->email, 404);

        $organization->notify(new CanalMessage($organization, $request->user(), $request->input('body')));

        return back()->with('flash', 'Správa bola odoslaná!');
    }


    public function toAdmin(StoreMessengerRequest $request) {

       // Odosielateľ sa berie z prihlásenia, nie z tela požiadavky. Kým sa
       // čítal z `user_id`, dala sa správa pripísať ktorémukoľvek užívateľovi.
       // Pri neprihlásenom odosielateľovi zostáva zástupné ID 1 ako doteraz.
       Messenger::create([
            'user_id' => auth()->id() ?? 1,
            'requested_user' => $request->input('requested_organization', 1),
            'body' => $request->input('body')
        ]);

        return back();
    }


    public function store(StoreMessengerRequest $request, Canal $organization) {

       $message = Messenger::create([
            'user_id' => auth()->user()->id,
            'requested_user' => $organization->id,
            'body' => $request->input('body')
        ]);

        if(request()->expectsJson()) {
            return $message;
        };

        return back();
    }


}
