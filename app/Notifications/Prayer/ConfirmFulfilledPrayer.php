<?php

namespace App\Notifications\Prayer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Len zvonček v administrácii. toMail() bol nepoužitý anglický stub
 * z `make:notification`, preto zmizol.
 */
class ConfirmFulfilledPrayer extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prayer;

    public function __construct($prayer)
    {
        $this->prayer = $prayer;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        // Modlitba nemá `user_id`, patrí kanálu — `$prayer->user` bol vždy null.
        $canal = $this->prayer->canal;
        $name = $canal?->user?->adminName() ?? $canal?->title;

        return [
            'logo' => $canal?->initialName,
            'message' => $name . ' potvrdil vypočutú modlitbu ' . $this->prayer->title,
            'link' => route('modlitby.index')
        ];
    }
}
