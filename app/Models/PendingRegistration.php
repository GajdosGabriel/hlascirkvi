<?php

namespace App\Models;

use App\Notifications\User\ConfirmRegistration;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Registrácia formulárom, ktorá ešte nebola potvrdená z e-mailu.
 *
 * Pokiaľ adresa nie je potvrdená, v `users` nie je nič — žiadny účet, žiadny
 * kanál, žiadna notifikácia administrátorom. Robotické a vymyslené registrácie
 * tak skončia tu a po PendingRegistration::TTL_DAYS ich zmaže model:prune.
 */
class PendingRegistration extends Model
{
    use MassPrunable, Notifiable;

    /** Ako dlho platí odkaz z e-mailu (a ako dlho záznam čaká). */
    public const TTL_DAYS = 7;

    /** Minimálny odstup medzi dvoma potvrdzovacími e-mailmi na jednu adresu. */
    public const RESEND_AFTER_SECONDS = 120;

    /** Viac e-mailov na jednu adresu neodíde — chráni cudzie schránky pred zaplavením. */
    public const MAX_SENDS = 5;

    /** Po koľkých dňoch bez potvrdenia príde (jediná) pripomienka. */
    public const REMIND_AFTER_DAYS = 3;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'token'];

    protected $casts = [
        'sent_at' => 'datetime',
        'reminded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', static::hashToken($token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Nový token (starý odkaz tým prestane platiť), predĺžená platnosť
     * a e-mail s odkazom. Vracia false, ak by šlo o príliš časté posielanie.
     */
    public function sendConfirmation(bool $isResend = false): bool
    {
        if ($isResend && ! $this->canResend()) {
            return false;
        }

        $token = $this->issueToken([
            'expires_at' => now()->addDays(self::TTL_DAYS),
            'send_count' => $isResend ? $this->send_count + 1 : 1,
        ] + ($isResend ? [] : ['reminded_at' => null]));

        $this->notify(new ConfirmRegistration($token));

        return true;
    }

    /**
     * Jediná automatická pripomienka (registrations:remind). Platnosť sa
     * nepredlžuje — registrácia tak stále vyprší po TTL_DAYS od odoslania
     * formulára, len s novým odkazom.
     */
    public function sendReminder(): void
    {
        $token = $this->issueToken([
            'reminded_at' => now(),
            'send_count' => $this->send_count + 1,
        ]);

        $this->notify(new ConfirmRegistration($token, reminder: true));
    }

    /** Čakajúce registrácie, ktorým je čas pripomenúť sa. */
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
            'sent_at' => now(),
        ])->save();

        return $token;
    }

    public function canResend(): bool
    {
        return $this->send_count < self::MAX_SENDS
            && ($this->sent_at === null || $this->sent_at->lte(now()->subSeconds(self::RESEND_AFTER_SECONDS)));
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function prunable()
    {
        // Bez expirácie zostane len záznam, ktorému sa nepodarilo odoslať ani
        // prvý e-mail; ten po dni tiež nemá význam.
        return static::where('expires_at', '<=', now())
            ->orWhere(fn ($q) => $q->whereNull('expires_at')->where('created_at', '<=', now()->subDay()));
    }
}
