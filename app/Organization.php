<?php

namespace App;

use App\Services\PhoneSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use Favoritable, Notifiable, SoftDeletes;
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

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
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
        //The strtoupper() function converts a string to uppercase.
        $name  = strtoupper($this->title);
        //prefixes that needs to be removed from the name
        $remove = ['.', 'MRS', 'MISS', 'MS', 'MASTER', 'DR', 'MR'];
        $nameWithoutPrefix = str_replace($remove, " ", $name);

        $words = explode(" ", $nameWithoutPrefix);

        //this will give you the first word of the $words array , which is the first name
        $firtsName = reset($words);

        //this will give you the last word of the $words array , which is the last name
        $lastName  = end($words);

        return substr($firtsName, 0, 1) . '' . substr($lastName, 0, 1); // this will echo the first letter of your last name
    }
}
