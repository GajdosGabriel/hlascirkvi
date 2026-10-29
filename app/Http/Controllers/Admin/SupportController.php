<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SupportStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportDesk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Schránka podpory — všetky vlákna používateľov. Správca tu odpovedá v mene
 * podpory; používateľ ich vidí v nástenke (App\Http\Controllers\SupportController).
 */
class SupportController extends Controller
{
    public function __construct(private readonly SupportDesk $desk)
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(SupportStatus::class)],
            'search' => ['nullable', 'string', 'max:250'],
        ]);

        $tickets = SupportTicket::query()
            ->with(['user:id,first_name,last_name,email', 'latestMessage'])
            ->withCount('messages')
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $term = '%'.addcslashes($search, '\\%_').'%';
                $q->where(fn (Builder $w) => $w
                    ->where('subject', 'like', $term)
                    ->orWhereHas('messages', fn (Builder $m) => $m->where('body', 'like', $term))
                    ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', $term)));
            })
            // Hore to, čo čaká na správcu.
            ->orderByRaw('CASE status WHEN ? THEN 0 WHEN ? THEN 1 ELSE 2 END',
                [SupportStatus::Open->value, SupportStatus::Answered->value])
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admins.support.index', [
            'tickets' => $tickets,
            'counts' => SupportTicket::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->desk->markSeen($supportTicket, $request->user(), true);

        return view('admins.support.show', [
            'ticket' => $supportTicket->load(['user', 'messages.author']),
            'otherTickets' => SupportTicket::where('user_id', $supportTicket->user_id)
                ->whereKeyNot($supportTicket->id)
                ->orderByDesc('id')
                ->limit(10)
                ->get(['id', 'subject', 'status', 'created_at']),
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $this->desk->reply($supportTicket, $request->user(), $data['body']);

        return redirect()->route('admin.support.show', $supportTicket)->with('flash', 'Odpoveď bola odoslaná.');
    }

    public function status(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate(['status' => ['required', Rule::enum(SupportStatus::class)]]);

        $this->desk->setStatus($supportTicket, SupportStatus::from($data['status']));

        return redirect()->route('admin.support.show', $supportTicket);
    }
}
