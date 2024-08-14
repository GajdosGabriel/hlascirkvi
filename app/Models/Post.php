<?php

namespace App\Models;


use Carbon\Carbon;
use App\Traits\HasRoute;
use App\Traits\HasImages;
use App\Traits\HasBigThink;
use App\Traits\HasComments;
use Illuminate\Support\Str;
use App\Casts\DateTimeHuman;
use App\Casts\VideoDuration;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasOrganization;
use App\Traits\HasDatetime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements Viewable
{
    use HasFactory, Notifiable, SoftDeletes, InteractsWithViews, HasFavorites, HasComments, HasImages, HasOrganization, HasBigThink, HasRoute, HasFilter, HasDatetime;

    protected $guarded = [];
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
        // 'created_at' => DateTimeHuman::class
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

    public function getPersonAttribute()
    {
        if ($this->user->organization) return $this->user->organization;
        return $this->user->fullname;
    }

   
    public function getHasUpdaterAttribute()
    {
        return $this->updaters()->exists();
    }

    

    public function getEventsBelongsToOrganizationAttribute()
    {
        return $this->organization->events()->wherePublished(1)->where('start_at', '>', Carbon::now())->orderBy('start_at', 'asc')->paginate(10);
    }

    public function scopeUnpublished()
    {
        return $this->wherePublished(null);
    }


}
