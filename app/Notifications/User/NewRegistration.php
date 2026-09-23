<?php

namespace App\Notifications\User;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Administrátorom pri každom novom overenom účte (App\Services\UserActivation).
 */
class NewRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('Nová registrácia: '.$this->user->adminName())
            ->line('na portáli pribudol nový používateľský účet.')
            ->details([
                'Meno' => $this->user->adminName(),
                'E-mail' => $this->user->email,
                'Založený' => $this->user->created_at?->format('d.m.Y H:i'),
                'E-mail overený' => $this->user->hasVerifiedEmail() ? 'áno' : 'zatiaľ nie',
            ])
            ->action('Otvoriť v administrácii', route('admin.user.edit', $this->user));
    }
}
