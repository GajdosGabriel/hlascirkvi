<?php

namespace App\Models;

use Storage;
use Carbon\Carbon;
use App\Models\Favoritable;
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
    use Favoritable, HasFactory, Notifiable, SoftDeletes, InteractsWithViews;

    protected $guarded = [];
    protected $hidden = ['blocked', 'youtube_blocked', 'deleted_at'];

    protected $with = ['favorites', 'images'];
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bigThinks()
    {
        return $this->hasMany(BigThink::class);
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

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'fileable');
    }

    public function addComment($comment)
    {
        if (auth()->check()) {
            $comment = $this->comments()->create(array_merge($comment, ['user_id' => auth()->id()]));
            return $comment;
        }
        // user_id 100 in unknowle user for anonyms comments
        $comment = $this->comments()->create(array_merge($comment, ['user_id' => 100]));
        $comment->delete();

        return $comment;
    }

    public function addBigThink($comment)
    {
        if (auth()->user()) {
            $idUser = auth()->user()->organizations()->wherePerson(1)->first()->id;
        } else {
            $idUser =  1;
        }
        return $this->bigThinks()->create(array_merge($comment, ['organization_id' => $idUser]));
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

    public function destroyImages()
    {

        if ($this->images()->exists()) {

            foreach ($this->images as $image) {
                // delete big img
                Storage::delete('public/' . $image->url);

                // delete small img
                Storage::delete('public/' . $image->thumb);

                $image->delete();
            }
        }
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

    public function getThumbImageAttribute()
    {
        $image = $this->images->first();
        if ($image) {
            return url($image->ThumbImageUrl);
        }

        if ($this->organization->avatar) {
            return Storage::url('organizations/' . $this->organization->id . '/' . $this->organization->avatar);
        }

        return url('images/foto.jpg');
    }

    public function getEventsBelongsToOrganizationAttribute()
    {
        return $this->organization->events()->wherePublished(1)->where('start_at', '>', Carbon::now())->orderBy('start_at', 'asc')->paginate(10);
    }
}
