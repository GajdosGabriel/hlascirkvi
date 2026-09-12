<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Zdroj videí kanála sa na YouTube nenašiel, takže sťahovanie je vypnuté
 * (App\Services\Youtube\DisableImport). Chodí superadminovi do zvončeka
 * na nástenke — v logu si to nikto nevšimol a tá istá chyba sa opakovala
 * každý deň.
 */
class YoutubeSourceMissing extends Notification
{
    use Queueable;

    public function __construct(
        protected $canal,
        protected string $reason,
    ) {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->canal->title . ': sťahovanie videí z YouTube je vypnuté — ' . $this->reason,
            'link' => route('profile.canals.edit', $this->canal->id),
            'logo' => 'YT',
            'canal_id' => $this->canal->id,
        ];
    }
}
