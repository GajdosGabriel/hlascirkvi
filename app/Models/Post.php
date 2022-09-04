<?php

namespace App\Models;


use DateInterval;
use Carbon\Carbon;
use App\Traits\HasBigThink;
use App\Traits\HasComments;

use App\Traits\HasFavorites;
use App\Traits\HasImages;
use App\Traits\HasOrganization;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements Viewable
{
    use HasFactory, Notifiable, SoftDeletes, InteractsWithViews, HasFavorites, HasComments, HasImages, HasOrganization, HasBigThink;

    protected $guarded = [];
    protected $hidden = ['blocked', 'youtube_blocked', 'deleted_at'];

    protected $with = ['favorites', 'images', 'organization'];
    protected $appends = ['favoritesCount', 'isFavorited', 'url', 'thumbImage', 'createdAtHuman', 'hasUpdater'];

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

    /**
     * @return bool|string
     */
    public function getDatetimeAttribute()
    {
        return date('d m Y', strtotime($this->created_at));
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


    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }


    public function getPersonAttribute()
    {
        if ($this->user->organization) return $this->user->organization;
        return $this->user->fullname;
    }

    public function getUrlAttribute()
    {
        return route('post.show', [$this->id, $this->slug]);
    }

    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getHasUpdaterAttribute()
    {
        return $this->updaters()->exists();
    }

    

    public function getEventsBelongsToOrganizationAttribute()
    {
        return $this->organization->events()->wherePublished(1)->where('start_at', '>', Carbon::now())->orderBy('start_at', 'asc')->paginate(10);
    }

    public function video_duration()
    {
        if ($this->video_duration) {
            $duration = new DateInterval($this->video_duration);
            // return $duration->h;
            return "{$duration->i}:{$duration->s}";
        }

        return false;
    }
}
