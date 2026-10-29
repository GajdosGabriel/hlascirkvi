<?php

namespace App\Services;

use App\Enums\SupportCategory;
use App\Enums\SupportStatus;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Support\SupportTicketCreated;
use App\Notifications\Support\SupportTicketReplied;
use App\Services\SystemLog\Recorder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Podpora — vlákna používateľa so správcami webu.
 *
 * Používateľ píše z päty alebo z nástenky (Podpora), správca odpovedá
 * v administrácii. Každá nová správa ide druhej strane e-mailom aj do zvončeka.
 * Spoločné pre Canal\SupportController aj Admin\SupportController.
 */
class SupportDesk
{
    /** Správcovia, ktorí podporu vybavujú — tí istí, čo majú prístup do /admin. */
    public function staff(): Collection
    {
        return User::role('superadmin')->get();
    }

    public function isStaff(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * @param  array{category: string, subject?: string|null, body: string, page_url?: string|null}  $data
     */
    public function open(User $user, array $data, ?string $userAgent = null): SupportTicket
    {
        $body = trim($data['body']);
        // Päta sa na predmet nepýta — stačí začiatok správy.
        $subject = trim((string) ($data['subject'] ?? '')) ?: Str::limit(Str::squish($body), 80);

        $ticket = DB::transaction(function () use ($user, $data, $body, $subject, $userAgent) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'category' => SupportCategory::from($data['category']),
                'subject' => $subject,
                'status' => SupportStatus::Open,
                'page_url' => isset($data['page_url']) ? Str::limit($data['page_url'], 490, '') : null,
                'user_agent' => $userAgent ? Str::limit($userAgent, 490, '') : null,
                'last_activity_at' => now(),
                'user_seen_at' => now(),
            ]);

            $ticket->messages()->create([
                'user_id' => $user->id,
                'is_staff' => false,
                'body' => $body,
            ]);

            return $ticket;
        });

        Notification::send($this->staff()->reject(fn (User $u) => $u->is($user)), new SupportTicketCreated($ticket));

        // Text správy je súkromný, do denníka ide len to, že vlákno vzniklo.
        Recorder::activity('support', 'created', 'Nová správa podpore '.$ticket->reference(), $ticket, $user->id);

        return $ticket;
    }

    /**
     * Odpoveď vo vlákne. Vlastník píše ako používateľ aj vtedy, keď je
     * náhodou správca; inak píše podpora.
     */
    public function reply(SupportTicket $ticket, User $author, string $body): SupportMessage
    {
        $asStaff = (int) $ticket->user_id !== (int) $author->id;

        $message = DB::transaction(function () use ($ticket, $author, $body, $asStaff) {
            $message = $ticket->messages()->create([
                'user_id' => $author->id,
                'is_staff' => $asStaff,
                'body' => trim($body),
            ]);

            // Odpoveď vlákno vždy otvorí — aj uzavreté, keď sa k nemu niekto
            // vráti. Druhá strana tým dostane „nové".
            $ticket->forceFill([
                'status' => $asStaff ? SupportStatus::Answered : SupportStatus::Open,
                'last_activity_at' => now(),
                'user_seen_at' => $asStaff ? null : now(),
                'staff_seen_at' => $asStaff ? now() : null,
            ])->save();

            return $message;
        });

        $notification = new SupportTicketReplied($message);

        if ($asStaff) {
            $ticket->user?->notify($notification);
        } else {
            Notification::send($this->staff()->reject(fn (User $u) => $u->is($author)), $notification);
        }

        Recorder::activity('support', 'replied', 'Odpoveď vo vlákne podpory '.$ticket->reference(), $ticket, $author->id,
            ['is_staff' => $asStaff]);

        return $message;
    }

    public function setStatus(SupportTicket $ticket, SupportStatus $status): void
    {
        $ticket->forceFill(['status' => $status, 'last_activity_at' => now()])->save();
    }

    /** Otvorenie vlákna je jeho prečítanie — aj upozornenia k nemu už netreba. */
    public function markSeen(SupportTicket $ticket, User $viewer, bool $asStaff): void
    {
        $ticket->forceFill([$asStaff ? 'staff_seen_at' : 'user_seen_at' => now()])->save();

        $viewer->unreadNotifications()
            ->whereIn('type', [SupportTicketCreated::class, SupportTicketReplied::class])
            ->where('data->ticket_id', $ticket->id)
            ->update(['read_at' => now()]);
    }

    /** Počet mojich vlákien s nepozretou odpoveďou podpory. */
    public function unreadFor(User $user): int
    {
        return SupportTicket::where('user_id', $user->id)->whereNull('user_seen_at')->count();
    }
}
