<?php

namespace App\Models;

use App\Models\Concerns\ConfirmableByEmail;
use App\Notifications\Prayer\ConfirmPrayer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

/**
 * Modlitba od neprihláseného, ktorej autor ešte nepotvrdil e-mail.
 *
 * Kým adresa nie je potvrdená, v `users` ani `prayers` nie je nič. Po kliknutí
 * na odkaz Public\PrayerController::confirm založí (alebo overí) účet
 * a modlitbu zverejní.
 */
class PendingPrayer extends Model
{
    use ConfirmableByEmail;

    protected $guarded = ['id'];

    protected $casts = [
        'title' => \App\Casts\StringLength255::class,
    ];

    protected function confirmationNotification(string $token, bool $reminder): Notification
    {
        return new ConfirmPrayer($token, $this->expires_at, $reminder);
    }
}
