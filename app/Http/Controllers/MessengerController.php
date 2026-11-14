<?php

namespace App\Http\Controllers;

use App\Models\Canal;
use App\Http\Requests\StoreMessengerRequest;
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
}
