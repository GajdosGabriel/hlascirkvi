<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasRoles;

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
        'password', 'remember_token', 'email', 'send_email', 'front_author', 'disabled', 'updated_at', 'deleted_at', 'set_denomination', 'org_id', 'email_verified_at', 'vocative'
    ];

    protected $appends = ['fullname'];
    protected $casts = [
        'created_at'
    ];

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
        $this->attributes['last_name'] = ucfirst($value);
        $this->attributes['slug'] = Str::slug($this->attributes['first_name'] . $this->attributes['last_name'], '-');
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
