<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

use CyrildeWit\EloquentViewable\Viewable;
use CyrildeWit\EloquentViewable\Contracts\Viewable as ViewableContract;
use Illuminate\Support\Str;

class Event extends Model implements ViewableContract
{
    use Favoritable, SoftDeletes, Viewable;
    protected $guarded = [];

    protected $dates = ['end_at'];
//    protected $with = ['village', 'organization'];

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


    public function images() {
        return $this->morphMany(Image::class, 'fileable');
    }

    public function comments() {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }


    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = cleanTitle( ucfirst($value));
        $this->attributes['slug']  = Str::slug($value);
    }

    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = cleanBody($value);
    }

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = $value;
        $this->attributes['end_at'] = Carbon::create($value)->addHours(2);
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
        if( $this->start_at > Carbon::now())
        {
            return true;
        }
    }


    public function subscribe()
    {
        if ($this->eventSubscribe()->whereOrganizationId(auth()->id() )->exists() ) {

            if($this->eventSubscribe()->whereOrganizationId(auth()->id())->where('active', 0)->exists() ) {

                $this->eventSubscribe()->update(['active' => 1] );
                session()->flash('flash', 'Ste prihlásený na akciu!!');
            } else {
                $this->eventSubscribe()->update(['active' => 0] );
                session()->flash('flash', 'Ste odhlásený z akcie!');
            }

        } else {
            $this->eventSubscribe()->create( ['organization_id' => auth()->id()] );
            session()->flash('flash', 'Ste prihlásený na akciu!');
        }
    }


    public function isSubscribed() {
        return ! ! $this->eventSubscribe->where('organization_id', auth()->id())->where('active', 1)->count();
//        return $this->favorites()->whereUserId(auth()->id())->exists();
    }

    public function activeSubscribed() {
        return $this->eventSubscribe()->whereActive(1)->count();
    }



}
