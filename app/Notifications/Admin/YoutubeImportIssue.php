<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Chyba denného importu videí, ktorú vie správca opraviť vo formulári kanála
 * (zlá adresa kanála, neexistujúci playlist). Doteraz končila v logu, kde si
 * ju nikto neprečítal — chodí preto do zvončeka na nástenke aj s odkazom
 * priamo na formulár.
 *
 * `key` odlišuje druh chyby, aby to isté hlásenie nechodilo z každého denného
 * behu znova — pozri App\Services\Youtube\NotifyAdmin.
 */
class YoutubeImportIssue extends Notification
{
    use Queueable;

    public function __construct(
        protected $canal,
        protected string $key,
        protected string $message,
    ) {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'link' => route('profile.canals.edit', $this->canal->id),
            'logo' => 'YT',
            'canal_id' => $this->canal->id,
            'key' => $this->key,
        ];
    }
}
