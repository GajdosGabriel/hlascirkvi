<?php

namespace App\Notifications\Comments;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Tomu, kto odpovedal hosťovi z YouTube, keď za hosťa zareagoval portál
 * (App\Services\GuestReplier). Hosť sám odpovedať nemôže, preto okrem
 * zvončeka ide aj e-mail — inak by sa o odpovedi nemusel dozvedieť.
 */
class GuestRepliedToComment extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  \App\Models\Comment  $reply  odpoveď portálu
     * @param  \App\Models\Comment  $answered  odpoveď príjemcu, na ktorú sa reaguje
     */
    public function __construct(protected $reply, protected $answered)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $commentable = $this->reply->commentable;
        $title = $commentable?->title ?? 'príspevku';

        return PortalMail::for($notifiable)
            ->subject("Odpoveď na váš komentár pri {$title}")
            ->line("pri príspevku „{$title}“ ste odpovedali na komentár z YouTube:")
            ->quote($this->answered->body, 'Vaša odpoveď')
            ->line('Autor komentára na našom webe odpovedať nemôže, preto vám odpisujeme za portál:')
            ->quote($this->reply->body, $this->reply->user_name)
            ->action('Zobraziť diskusiu', url($commentable?->path() ?? '/'));
    }

    public function toArray($notifiable)
    {
        $commentable = $this->reply->commentable;

        return [
            'message' => $this->reply->user_name . ' odpovedal na váš komentár pri ' . ($commentable?->title ?? 'príspevku'),
            'link' => $commentable?->path(),
        ];
    }
}
