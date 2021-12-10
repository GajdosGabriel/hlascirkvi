<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use  HasApiTokens, Notifiable, SoftDeletes, HasRoles;

    protected $with = ['roles', 'permissions'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email', 'send_email', 'front_author', 'disabled', 'updated_at', 'deleted_at', 'set_denomination', 'email_verified_at', 'vocative'
    ];


    protected $casts = [
        'created_at',
        // 'notify_bell'
    ];

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);

    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }



    public function addresBooks() {
        return $this->hasMany(AddresBook::class);
    }

    public function prayers() {
        return $this->hasMany(Prayer::class);
    }


    public function organizations() {
        return $this->belongsToMany(Organization::class);
    }


    public function userPictureUrl()
    {
        return 'users/' . $this->id . '/' . $this->avatar;
    }


    public function getFullnameAttribute()
    {
        return $this->last_name . ' '. $this->first_name;
    }

    public function getNameAttribute()
    {
        if($this->id == $this->person) return $this->getFullnameAttribute();
        return $this->organization;
    }

//Nahradené spatie
//    public function isAdmin()
//    {
//        return in_array($this->email, ['gajdosgabo@gmail.com'] );
//    }


    public function getPostsCountAttribute()
    {
        return $this->organizations()->count();
    }

    public function getOwnerAttribute()
    {
        return $this->organizations()->first();
    }





}
