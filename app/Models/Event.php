<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Favoritable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;

class Event extends Model implements Viewable
{
    use Favoritable, SoftDeletes, InteractsWithViews;
    protected $guarded = [];
    protected $appends = ['url'];



//    protected $with = ['village', 'organization'];

//    protected $casts = [
//        'start_at'  => 'datetime:Y-m-d H:i',
//        'end_at'    => 'datetime:Y-m-d H:i',
//    ];

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function eventSubscribe()
    {
        return $this->hasMany(EventSubscribe::class);
    }


    public function images()
    {
        return $this->morphMany(Image::class, 'fileable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }


    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = cleanTitle(ucfirst($value));
        $this->attributes['slug']  = Str::slug($value);
    }

    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = cleanBody($value);
    }

    public function getStartAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getEndAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function displayStatus()
    {
        if ($this->start_at > Carbon::now()) {
            return true;
        }
    }


    public function subscribe()
    {
        if ($this->eventSubscribe()->whereOrganizationId(auth()->id())->exists()) {
            if ($this->eventSubscribe()->whereOrganizationId(auth()->id())->where('active', 0)->exists()) {
                $this->eventSubscribe()->update(['active' => 1]);
                session()->flash('flash', 'Ste prihlásený na akciu!!');
            } else {
                $this->eventSubscribe()->update(['active' => 0]);
                session()->flash('flash', 'Ste odhlásený z akcie!');
            }
        } else {
            $this->eventSubscribe()->create(['organization_id' => auth()->id()]);
            session()->flash('flash', 'Ste prihlásený na akciu!');
        }
    }


    public function isSubscribed()
    {
        // return ! ! $this->eventSubscribe->where('organization_id', auth()->id())->where('active', 1)->count();
        return $this->favorites()->whereUserId(auth()->id())->exists();
    }

    public function activeSubscribed()
    {
        return $this->eventSubscribe()->whereActive(1)->count();
    }

    public function getUrlAttribute()
    {
        return route('event.show', [$this->id, $this->slug]);
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

    public function getImagethumbAttribute()
    {
        if ($this->images()->whereType('img')->exists()) {
            return "storage/{$this->images()->whereType('img')->first()->thumb}";
        }
        return false;
    }

    public function getImagecardAttribute()
    {
        if ($this->images()->whereType('card')->exists()) {
            return "storage/{$this->images()->whereType('card')->first()->thumb}";
        }
        return false;
    }
}
