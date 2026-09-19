<?php

namespace App\Models;

use App\Enums\ModelStatus;
use App\Notifications\User\ConfirmEmail;
use App\Notifications\User\ResetPassword;
use App\Traits\HasDatetime;
use App\Traits\HasFilter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read Canal|null $canal
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasDatetime, HasFactory, HasFilter, HasRoles, Notifiable, SoftDeletes;

    // roles potrebuje hasRole() prakticky pri každej požiadavke. Priame
    // permissions modelu sa nepoužívajú (oprávnenia visia na rolách), takže ich
    // eager load bol dopyt navyše ku každému načítaniu užívateľa — spatie si ich
    // v prípade potreby dotiahne sám.
    protected $with = ['roles'];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * Doteraz tu bolo $guarded = [], čiže hromadne zapisovateľné bolo všetko
     * vrátane `password`, `disabled`, `email_verified_at` a `canal_id`. V spojení
     * s `$user->update($request->all())` v API to znamenalo prevzatie účtu.
     *
     * Stavové stĺpce (disabled, email_verified_at, verified, api_token) sa
     * zámerne nastavujú priamym priradením tam, kde na to je dôvod.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'description',
        'slug',
        'send_email',
        'front_author',
        'set_denomination',
        'canal_id',
        'notify_bell',
        'vocative',
        'gender',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // api_token tu chýbal, takže sa posielal klientovi v každej serializácii
    // užívateľa — napríklad v odpovedi na pridanie komentára.
    protected $hidden = [
        'password', 'remember_token', 'api_token', 'email', 'send_email', 'front_author', 'disabled', 'status_changed_by', 'status_reason', 'last_login_ip', 'updated_at', 'deleted_at', 'set_denomination', 'email_verified_at', 'vocative',
    ];

    // 'created_at' tu bolo bez kľúča, takže skončilo pod indexom 0 a ako cast
    // sa nikdy neuplatnilo. Boolean stĺpce sa zároveň čítali ako reťazce "0"/"1".
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notify_bell' => 'datetime',
        'status' => ModelStatus::class,
        'status_changed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'disabled' => 'boolean',
        'send_email' => 'boolean',
        'front_author' => 'boolean',
        'verified' => 'boolean',
    ];

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }

    public function commentss()
    {
        return $this->hasMany(Comment::class);
    }

    public function canals()
    {
        return $this->belongsToMany(Canal::class);
    }

    /**
     * Aktívny kanál (canal_id) smie byť len kanál, ktorý užívateľ spravuje —
     * policy príspevkov a modlitieb mu veria. Po odobratí zo správcov sa preto
     * prepne na iný jeho kanál, a ak žiadny nemá, ostane prázdny.
     */
    public function resetActiveCanalIfNotManaged(): void
    {
        if ($this->canal_id === null
            || $this->canals()->whereKey($this->canal_id)->exists()) {
            return;
        }

        $this->update(['canal_id' => $this->canals()->min('canals.id')]);
    }

    /** Príspevky uložené na neskôr (stránka /ulozene). */
    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts')->withTimestamps();
    }

    public function canal()
    {
        return $this->belongsTo(Canal::class, 'canal_id');
    }

    public function userPictureUrl()
    {
        return 'users/'.$this->id.'/'.$this->avatar;
    }

    public function getFullnameAttribute()
    {
        return $this->last_name.' '.$this->first_name;
    }

    /**
     * `users` nemá stĺpec `person` (ten je na canals), takže podmienka
     * `$this->id == $this->person` nikdy neplatila a atribút vracal celý model
     * kanála. Laravel pritom číta $user->name pri Mail::to() ako meno príjemcu
     * — do hlavičky e-mailu tak išiel JSON celého riadku kanála.
     */
    public function getNameAttribute()
    {
        return $this->getFullnameAttribute();
    }

    public function getPostsCountAttribute()
    {
        return $this->canals()->count();
    }

    public function getOwnerAttribute()
    {
        return $this->canals()->first();
    }

    public function banned()
    {
        // `disabled` ostáva počas prechodného obdobia kvôli starším dátam a
        // prípadným integráciám. Nový enum je zdroj pravdy.
        return $this->disabled || ! $this->status->isActive();
    }

    /** @return array<int, ModelStatus> */
    public static function statusOptions(): array
    {
        return [
            ModelStatus::PendingReview,
            ModelStatus::Active,
            ModelStatus::Archived,
            ModelStatus::Blocked,
        ];
    }

    public function recordLogin(string $via, ?string $ip): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_via' => $via,
            'last_login_ip' => $ip,
        ])->saveQuietly();
    }

    /**
     * Stav účtu pre administráciu. ModelStatus hovorí, čo s účtom urobil
     * administrátor, overenie e-mailu zasa, či ho dokončil používateľ. Zelené
     * „Aktívny" preto svieti až pri oboch naraz — stav `active` s neoverenou
     * adresou je len rozbehnutá registrácia.
     *
     * Poradie: blokácia (vrátane starého `disabled`) > ostatné neaktívne stavy
     * > neoverený e-mail > aktívny.
     *
     * @return array{label: string, tone: 'green'|'amber'|'gray'|'red', title: ?string}
     */
    public function accountBadge(): array
    {
        if ($this->disabled || $this->status === ModelStatus::Blocked) {
            return ['label' => ModelStatus::Blocked->label(), 'tone' => 'red', 'title' => $this->status_reason];
        }

        if (! $this->status->isActive()) {
            return [
                'label' => $this->status->label(),
                'tone' => $this->status === ModelStatus::Archived ? 'gray' : 'amber',
                'title' => $this->status_reason,
            ];
        }

        if (! $this->hasVerifiedEmail()) {
            return ['label' => __('model_status.unverified'), 'tone' => 'amber', 'title' => __('model_status.unverified_hint')];
        }

        return [
            'label' => ModelStatus::Active->label(),
            'tone' => 'green',
            'title' => __('model_status.verified_at', ['date' => $this->email_verified_at->format('d.m.Y H:i')]),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function accountAccessMessage(): string
    {
        $status = $this->disabled && $this->status === ModelStatus::Active
            ? ModelStatus::Blocked
            : $this->status;

        return match ($status) {
            ModelStatus::PendingReview => 'Tento účet čaká na schválenie administrátorom.',
            ModelStatus::Blocked => 'Tento účet bol zablokovaný. Ak si myslíte, že ide o omyl, kontaktujte administrátora webu.',
            ModelStatus::Archived => 'Tento účet bol archivovaný. Ak ho chcete obnoviť, kontaktujte administrátora webu.',
            default => 'Tento účet momentálne nie je aktívny. Kontaktujte administrátora webu.',
        };
    }

    public function getLastLoginViaLabelAttribute(): ?string
    {
        return self::loginViaLabel($this->last_login_via);
    }

    public static function loginViaLabel(?string $via): ?string
    {
        return match ($via) {
            'password' => 'E-mail a heslo',
            'google' => 'Google',
            'facebook' => 'Facebook',
            default => $via,
        };
    }

    /**
     * Model je od zavedenia overovania MustVerifyEmail, takže naň platí
     * middleware `verified`, hasVerifiedEmail() aj poslucháč na udalosti
     * Registered. Laravel by ale poslal svoju anglickú šablónu — portál má
     * vlastnú, slovenskú (App\Notifications\User\ConfirmEmail).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ConfirmEmail($this));
    }

    /** Slovenská obnova hesla namiesto Laravelovej anglickej šablóny. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }
}
