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
    // Správa správcovi webu (formulár „Napíšte nám") je od 10/2026 vlákno
    // podpory — App\Http\Controllers\SupportController.

    /**
     * Správa pre kanál z jeho verejnej stránky. Kontakty kanála sa na stránke
     * nezobrazujú; ak kanál e-mail nemá, niet kam správu poslať.
     */
    public function toCanal(StoreMessengerRequest $request, Canal $canal) {

        abort_unless($canal->email, 404);

        $canal->notify(new CanalMessage($canal, $request->user(), $request->input('body')));

        \App\Services\SystemLog\Recorder::activity('message', 'sent', 'Správa kanálu: ' . $canal->title, $canal);

        return back()->with('flash', 'Správa bola odoslaná!');
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
