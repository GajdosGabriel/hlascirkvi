<?php

namespace App\Models;

use App\Models\Prayer;
use Illuminate\Support\Str;
use App\Services\PhoneSanitizer;
use App\Traits\HasComments;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use Notifiable, SoftDeletes, HasFactory, HasFavorites, HasImages, HasFilter, HasComments;
    protected $guarded = [];


    protected $appends = ['favoritesCount', 'isFavorited', 'initialName'];



    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function prayers()
    {
        return $this->hasMany(Prayer::class);
    }

    public function messengers()
    {
        return $this->hasMany(Messenger::class);
    }

    public function eventSunscribes()
    {
        return $this->hasMany(EventSubscribe::class);
    }


    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'org_id');
    }

    public function updaters()
    {
        return $this->belongsToMany(Updater::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function seminars()
    {
        return $this->hasMany(Seminar::class);
    }

    public function bigThings()
    {
        return $this->hasMany(BigThink::class);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst($value);
        $this->attributes['slug']  = Str::slug($value);
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] =  (new PhoneSanitizer($value))->getSanitized();

        $phone_numeric = filter_var($this->attributes['phone'], FILTER_SANITIZE_NUMBER_INT);
        $phone_numeric = str_replace(['+', '-'], '', $phone_numeric);

        $this->attributes['phone_numeric'] = $phone_numeric;
    }

    public function anonymizer()
    {
        return strtok($this->title, " ");
    }


    // Inicialy mena
    public function getInitialNameAttribute()
    {
        $acronym = '';
        foreach (explode(' ', $this->title) as $word) $acronym .= mb_substr($word, 0, 1, 'utf-8');
        return $acronym;
    }
}
