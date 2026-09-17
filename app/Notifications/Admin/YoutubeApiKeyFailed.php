<?php

namespace App\Notifications\Admin;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * YouTube odmieta API kľúč (neplatný, expirovaný, obmedzený kľúč alebo
 * vypnuté API). Stojí import videí aj synchronizácia komentárov a v logu
 * si to nikto nevšimol — preto ide e-mailom aj do zvončeka. Posiela
 * App\Services\Youtube\KeyFailureAlert najviac raz za deň.
 */
class YoutubeApiKeyFailed extends Notification
{
    use Queueable;

    private const CONSOLE = 'https://console.cloud.google.com/apis/credentials';

    public function __construct(
        protected string $error,
    ) {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('YouTube API odmieta kľúč — import videí stojí')
            ->line('YouTube Data API odmietlo API kľúč portálu. Kým sa to neopraví, nesťahujú sa nové videá ani komentáre.')
            ->line('Chyba: ' . $this->error)
            ->line('Skontrolujte kľúč a jeho obmedzenia v Google Cloud Console, potom `YOUTUBE_API_KEY` v `.env` na serveri a spustite `php artisan config:cache`.')
            ->action('Otvoriť Google Cloud Console', self::CONSOLE);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'YouTube API odmieta kľúč, import videí a komentárov stojí — ' . $this->error,
            'link' => self::CONSOLE,
            'logo' => 'YT',
        ];
    }
}
