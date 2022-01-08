<?php

namespace App\Models;

use App\Models\Favoritable;
use Illuminate\Support\Str;
use App\Services\PhoneSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use Favoritable, Notifiable, SoftDeletes, HasFactory;
    protected $guarded = [];


    protected $appends = ['favoritesCount', 'isFavorited', 'initialName'];



    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function messengers()
    {
        return $this->hasMany(Messenger::class);
    }


    public function users()
    {
        return $this->belongsToMany(User::class);
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


    public function images()
    {
        return $this->morphMany(Image::class, 'fileable');
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
        foreach(explode(' ', $this->title) as $word) $acronym .= mb_substr($word, 0, 1, 'utf-8');
        return $acronym;
    }
}
