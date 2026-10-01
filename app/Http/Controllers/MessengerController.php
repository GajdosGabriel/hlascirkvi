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
    /** Správca, ktorému putujú správy z `toAdmin()`. */
    private const ADMIN_ID = 1;

    /**
     * Správa pre kanál z jeho verejnej stránky. Kontakty kanála sa na stránke
     * nezobrazujú; ak kanál e-mail nemá, niet kam správu poslať.
     */
    public function toCanal(StoreMessengerRequest $request, Canal $canal) {

        abort_unless($canal->email, 404);

        $canal->notify(new CanalMessage($canal, $request->user(), $request->input('body')));

        return back()->with('flash', 'Správa bola odoslaná!');
    }


    public function toAdmin(StoreMessengerRequest $request) {

       // Odosielateľ aj adresát sa berú zo servera, nie z požiadavky: trasa
       // vyžaduje prihlásenie a správa vždy putuje správcovi (user ID 1).
       Messenger::create([
            'user_id' => auth()->id(),
            'requested_user' => self::ADMIN_ID,
            'body' => $request->input('body')
        ]);

        // Formulár v päte sa po odoslaní len znovu načítal, bez akéhokoľvek
        // potvrdenia. JSON volania (Messenger.vue) si potvrdenie ukážu samy.
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('flash', 'Správa bola odoslaná. Ďakujeme!');
    }


    public function store(StoreMessengerRequest $request, Canal $canal) {

       Messenger::create([
            'user_id' => auth()->id(),
            'requested_user' => $canal->id,
            'body' => $request->input('body')
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }


}
