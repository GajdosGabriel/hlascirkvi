<?php

namespace App\Models;


use App\Traits\HasRoute;
use App\Traits\HasImages;
use App\Traits\HasComments;
use Illuminate\Support\Str;
use App\Casts\VideoDuration;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasOrganization;
use App\Traits\HasDatetime;
use App\Traits\HasViews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasViews, HasFavorites, HasComments, HasImages, HasOrganization, HasRoute, HasFilter, HasDatetime;

    // $guarded a $fillable naraz nedávajú zmysel — Eloquent uprednostní
    // $fillable a $guarded ignoruje, takže tu len mätlo. Platí zoznam nižšie.
    protected $hidden = ['blocked', 'youtube_blocked', 'deleted_at'];

    protected $with = ['favorites', 'images', 'organization'];
    protected $appends = ['favoritesCount', 'isFavorited', 'thumbImage', 'hasUpdater'];

    protected $fillable = [
        'organization_id',
        'title',
        'body',
        'slug',
        'youtube_blocked',
        'youtube',
        'video_id',
        'count_view',
        'published',
        'video_available',
        'video_duration',
    ];

    protected $casts = [
        'video_duration' => VideoDuration::class,
        'title' => \App\Casts\StringLength255::class,
    ];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('youtube_blocked', function (Builder $builder) {
            $builder->whereYoutubeBlocked(0);
        });
    }

    public function path()
    {
        return "/post/{$this->id}/{$this->slug}";
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function seminars()
    {
        return $this->belongsToMany(Seminar::class);
    }

    public function updaters()
    {
        return $this->belongsToMany(Updater::class);
    }


    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = cleanBody($value);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = cleanTitle(ucfirst($value));
        $this->attributes['slug']  = Str::slug($value);
    }


    public function getHasUpdaterAttribute()
    {
        // Atribút je v $appends, takže sa počíta pri každej serializácii. Bez
        // týchto dvoch skratiek to bol jeden exists() dopyt na každý príspevok
        // vo výpise; withExists('updaters') alebo eager load ho ušetria.
        if (array_key_exists('updaters_exists', $this->attributes)) {
            return (bool) $this->attributes['updaters_exists'];
        }

        if ($this->relationLoaded('updaters')) {
            return $this->updaters->isNotEmpty();
        }

        return $this->updaters()->exists();
    }



    public function scopeUnpublished()
    {
        return $this->wherePublished(null);
    }
}
