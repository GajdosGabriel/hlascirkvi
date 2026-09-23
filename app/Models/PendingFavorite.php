<?php

namespace App\Models;

use App\Models\Concerns\ConfirmableByEmail;
use App\Notifications\User\ConfirmFavorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

/**
 * „Pripojiť sa k modlitbe" (alebo odber kanála) od neprihláseného, ktorý ešte
 * nepotvrdil e-mail.
 *
 * Kým adresa nie je potvrdená, v `users` ani `favorites` nie je nič. Po
 * kliknutí na odkaz App\Services\PendingConfirmation založí (alebo overí)
 * účet a označenie pridá.
 */
class PendingFavorite extends Model
{
    use ConfirmableByEmail;

    protected $guarded = ['id'];

    public function favorited()
    {
        return $this->morphTo();
    }

    /** Pripojiť sa dá k viacerým modlitbám naraz, preto viac ako pri modlitbách. */
    public static function maxPerEmail(): int
    {
        return 10;
    }

    protected function confirmationNotification(string $token, bool $reminder): Notification
    {
        return new ConfirmFavorite($token, $this->expires_at, $reminder);
    }
}
