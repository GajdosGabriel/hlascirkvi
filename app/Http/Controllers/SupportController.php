<?php

namespace App\Http\Controllers;

use App\Enums\SupportStatus;
use App\Models\SupportTicket;
use App\Services\SupportDesk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Podpora z pohľadu používateľa — jeho vlákna so správcami webu.
 *
 * Nahradila formulár „Napíšte nám", ktorý len poslal správcovi e-mail:
 * správa teraz ostane zaevidovaná a odpoveď príde sem aj e-mailom.
 * Správca odpovedá v Admin\SupportController.
 */
class SupportController extends Controller
{
    public function __construct(private readonly SupportDesk $desk) {}

    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with('latestMessage')
            ->withCount('messages')
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('support.index', ['tickets' => $tickets]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'body' => ['required', 'string', 'min:3', 'max:5000'],
            // Len http(s) — v administrácii z neho je odkaz.
            'page_url' => ['nullable', 'url:http,https', 'max:500'],
        ], [
            'subject.required' => 'Doplňte predmet správy.',
            'subject.min' => 'Predmet musí mať aspoň 3 znaky.',
            'body.required' => 'Napíšte, s čím vám môžeme pomôcť.',
            'body.min' => 'Správa musí mať aspoň 3 znaky.',
        ]);

        $ticket = $this->desk->open($request->user(), $data, $request->userAgent());

        return redirect()->route('profile.support.show', $ticket)
            ->with('flash', 'Správa bola odoslaná. Odpoveď nájdete tu aj v e-maile.');
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeOwner($request, $supportTicket);

        $this->desk->markSeen($supportTicket, $request->user(), false);

        return view('support.show', [
            'ticket' => $supportTicket->load(['messages.author']),
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeOwner($request, $supportTicket);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $this->desk->reply($supportTicket, $request->user(), $data['body']);

        return redirect()->route('profile.support.show', $supportTicket)->with('flash', 'Správa bola odoslaná.');
    }

    /** Používateľ vlákno uzavrie (vyriešené) alebo znovu otvorí. */
    public function status(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeOwner($request, $supportTicket);

        $data = $request->validate([
            'status' => ['required', Rule::in([SupportStatus::Open->value, SupportStatus::Closed->value])],
        ]);

        $this->desk->setStatus($supportTicket, SupportStatus::from($data['status']));

        return redirect()->route('profile.support.show', $supportTicket);
    }

    /** Cudzie vlákno sa „neexistuje" — 404, nie 403, nech sa nedá hádať ID. */
    private function authorizeOwner(Request $request, SupportTicket $ticket): void
    {
        abort_unless((int) $ticket->user_id === (int) $request->user()->id, 404);
    }
}
