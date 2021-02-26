<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\Comments\CreatedNewComment;
use Illuminate\Support\Str;
use Storage;

use CyrildeWit\EloquentViewable\Viewable;
use CyrildeWit\EloquentViewable\Contracts\Viewable as ViewableContract;

class Post extends Model implements ViewableContract
{
    use Favoritable, Notifiable, SoftDeletes, Viewable;

    protected $guarded = [];
    protected $hidden = ['organization_id', 'blocked', 'youtube_blocked', 'deleted_at'];

    protected $with = ['comments', 'favorites', 'organization', 'images'];
    protected $appends = ['favoritesCount', 'isFavorited', 'organization_name', 'url'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('youtube_blocked', function (Builder $builder) {
            $builder->whereYoutubeBlocked(0);
        });

        // Ak má byť publikované must have same records in updaters.
        static::addGlobalScope('published', function (Builder $builder) {
            $builder->has('updaters');
        });
    }

    public function path() {
        return "/post/{$this->id}/{$this->slug}";
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function bigThinks() {
        return $this->hasMany(BigThink::class);
    }

    public function updaters() {
        return $this->belongsToMany(Updater::class);
    }

    public function comments() {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }

    public function addComment($comment) {
        if( auth()->check()) {
            $comment = $this->comments()->create(array_merge($comment, ['user_id' => auth()->id()]));
            return $comment;
        }
            // user_id 100 in unknowle user for anonyms comments
            $comment = $this->comments()->create(array_merge($comment, ['user_id' => 100]));
            $comment->delete();

        return $comment;
    }

    public function addBigThink($comment) {
        if(auth()->user()) {
            $idUser = auth()->user()->organizations()->wherePerson(1)->first()->id;
        } else {
            $idUser =  1;
        }
        return $this->bigThinks()->create(array_merge($comment, ['organization_id' => $idUser]));

    }


    public function images() {
        return $this->morphMany(Image::class, 'fileable');
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

    public function destroyImages() {

        if($this->images()->exists()) {

            foreach( $this->images as $image)
            {
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


    public function getPersonAttribute() {
        if($this->user->organization) return $this->user->organization;
        return $this->user->fullname;
    }

    public function getOrganizationNameAttribute()
    {
        return $this->organization->title;
    }
    public function getUrlAttribute()
    {
        return route('post.show', [$this->title, $this->slug]);
    }













}
