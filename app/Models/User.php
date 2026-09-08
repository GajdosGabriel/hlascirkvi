<?php

namespace App\Models;

use App\Traits\HasDatetime;
use App\Traits\HasFilter;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use  HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasFilter, HasDatetime;

    // roles potrebuje hasRole() prakticky pri každej požiadavke. Priame
    // permissions modelu sa nepoužívajú (oprávnenia visia na rolách), takže ich
    // eager load bol dopyt navyše ku každému načítaniu užívateľa — spatie si ich
    // v prípade potreby dotiahne sám.
    protected $with = ['roles'];

    /**
     * The attributes that are mass assignable.
     *
     * Doteraz tu bolo $guarded = [], čiže hromadne zapisovateľné bolo všetko
     * vrátane `password`, `disabled`, `email_verified_at` a `org_id`. V spojení
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
        'org_id',
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
        'password', 'remember_token', 'api_token', 'email', 'send_email', 'front_author', 'disabled', 'updated_at', 'deleted_at', 'set_denomination', 'email_verified_at', 'vocative'
    ];


    // 'created_at' tu bolo bez kľúča, takže skončilo pod indexom 0 a ako cast
    // sa nikdy neuplatnilo. Boolean stĺpce sa zároveň čítali ako reťazce "0"/"1".
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notify_bell' => 'datetime',
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



    public function addresBooks()
    {
        return $this->hasMany(AddresBook::class);
    }

    public function commentss()
    {
        return $this->hasMany(Comment::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }


    public function userPictureUrl()
    {
        return 'users/' . $this->id . '/' . $this->avatar;
    }

    public function getFullnameAttribute()
    {
        return $this->last_name . ' ' . $this->first_name;
    }

    /**
     * `users` nemá stĺpec `person` (ten je na organizations), takže podmienka
     * `$this->id == $this->person` nikdy neplatila a atribút vracal celý model
     * kanála. Laravel pritom číta $user->name pri Mail::to() ako meno príjemcu
     * — do hlavičky e-mailu tak išiel JSON celého riadku organizácie.
     */
    public function getNameAttribute()
    {
        return $this->getFullnameAttribute();
    }

    public function getPostsCountAttribute()
    {
        return $this->organizations()->count();
    }

    public function getOwnerAttribute()
    {
        return $this->organizations()->first();
    }

    public function banned()
    {
        return $this->disabled;
    }
}
