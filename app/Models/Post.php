<?php

namespace App\Models;


use App\Enums\PostSection;
use App\Traits\HasRoute;
use App\Traits\HasImages;
use App\Traits\HasComments;
use Illuminate\Support\Str;
use App\Casts\VideoDuration;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasCanal;
use App\Traits\HasDatetime;
use App\Traits\HasViews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasViews, HasFavorites, HasComments, HasImages, HasCanal, HasRoute, HasFilter, HasDatetime;

    // $guarded a $fillable naraz nedávajú zmysel — Eloquent uprednostní
    // $fillable a $guarded ignoruje, takže tu len mätlo. Platí zoznam nižšie.
    protected $hidden = ['blocked', 'youtube_blocked', 'deleted_at'];

    protected $with = ['favorites', 'images', 'organization'];
    protected $appends = ['favoritesCount', 'isFavorited', 'thumbImage', 'isPublished'];

    protected $fillable = [
        'organization_id',
        'title',
        'body',
        'slug',
        'youtube_blocked',
        'youtube',
        'video_id',
        'count_view',
        'published_at',
        'section',
        'video_available',
        'video_duration',
    ];

    protected $casts = [
        'video_duration' => VideoDuration::class,
        'title' => \App\Casts\StringLength255::class,
        'published_at' => 'datetime',
        'section' => \App\Enums\PostSection::class,
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

    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = cleanBody($value);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = cleanTitle(ucfirst($value));
        $this->attributes['slug']  = Str::slug($value);
    }


    /**
     * Je príspevok vonku? Do 9/2026 sa to zisťovalo cez `hasUpdater`, teda
     * existenciou riadku v `post_updater` — a keďže atribút je v $appends,
     * bol to jeden exists() dopyt na každý príspevok vo výpise, kým si ho
     * volajúci neošetril cez withExists(). Teraz je to obyčajný stĺpec.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }

    /** Zverejnené príspevky — tie, ktoré publisher vypustil z buffera. */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /** Príspevky čakajúce vo fronte. */
    public function scopeUnpublished($query)
    {
        return $query->whereNull('published_at');
    }

    /** Príspevky jedného výpisu — úvodná stránka, prenosy, konferencie. */
    public function scopeSection($query, PostSection $section)
    {
        return $query->where('section', $section);
    }
}
