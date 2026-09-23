<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Čakáreň príspevku od neprihláseného (modlitba, komentár). Do `users` sa
 * zapisuje len overený účet, preto záznam čaká, kým autor neklikne na odkaz
 * z e-mailu. Po TTL_DAYS ho zmaže model:prune, po REMIND_AFTER_DAYS dostane
 * autor jednu pripomienku (pending:remind).
 *
 * Tabuľka potrebuje stĺpce email, token, sent_at, reminded_at, expires_at.
 */
trait ConfirmableByEmail
{
    use MassPrunable, Notifiable;

    /** Ako dlho platí odkaz z e-mailu (a ako dlho záznam čaká). */
    public const TTL_DAYS = 7;

    /** Viac nepotvrdených záznamov na jednu adresu neprijmeme — chráni cudzie schránky. */
    public const MAX_PER_EMAIL = 3;

    /** Po koľkých dňoch bez potvrdenia príde (jediná) pripomienka. */
    public const REMIND_AFTER_DAYS = 3;

    /** E-mail s odkazom; $reminder je automatická pripomienka. */
    abstract protected function confirmationNotification(string $token, bool $reminder): Notification;

    public function initializeConfirmableByEmail(): void
    {
        $this->mergeCasts([
            'sent_at' => 'datetime',
            'reminded_at' => 'datetime',
            'expires_at' => 'datetime',
        ]);
        $this->makeHidden('token');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByToken(string $token): ?static
    {
        return static::where('token', static::hashToken($token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /** Platné (nevypršané) čakajúce záznamy s danou adresou. */
    public static function forEmail(string $email)
    {
        return static::where('email', $email)->where('expires_at', '>', now());
    }

    public function sendConfirmation(): void
    {
        $token = $this->issueToken(['expires_at' => now()->addDays(self::TTL_DAYS)]);

        $this->notify($this->confirmationNotification($token, false));
    }

    /**
     * Jediná automatická pripomienka. Platnosť sa nepredlžuje — záznam stále
     * vyprší po TTL_DAYS, len s novým odkazom.
     */
    public function sendReminder(): void
    {
        $token = $this->issueToken(['reminded_at' => now()]);

        $this->notify($this->confirmationNotification($token, true));
    }

    /** Čakajúce záznamy, ktorým je čas pripomenúť sa. */
    public static function dueForReminder()
    {
        return static::whereNull('reminded_at')
            ->where('sent_at', '<=', now()->subDays(self::REMIND_AFTER_DAYS))
            ->where('expires_at', '>', now());
    }

    /**
     * Nový token do odkazu; starý tým prestane platiť. Vracia token
     * v čistej podobe, v databáze zostane len jeho hash.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function issueToken(array $attributes): string
    {
        $token = Str::random(64);

        $this->forceFill($attributes + [
            'token' => static::hashToken($token),
            'sent_at' => $this->sent_at ?? now(),
        ])->save();

        return $token;
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function prunable()
    {
        return static::where('expires_at', '<=', now())
            ->orWhere(fn ($q) => $q->whereNull('expires_at')->where('created_at', '<=', now()->subDay()));
    }
}
