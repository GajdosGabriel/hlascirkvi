<?php

namespace App\Models;

use Carbon\Carbon;

use App\Traits\HasComments;
use App\Traits\HasFavorites;
use App\Traits\HasImages;
use App\Traits\HasOrganization;
use App\Traits\HasRoute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model implements Viewable
{
    use SoftDeletes, InteractsWithViews, HasFactory, HasFavorites, HasComments, HasImages, HasOrganization, HasRoute;
    protected $guarded = [];
    protected $appends = [];



    //    protected $with = ['village', 'organization'];

    //    protected $casts = [
    //        'start_at'  => 'datetime:Y-m-d H:i',
    //        'end_at'    => 'datetime:Y-m-d H:i',
    //    ];

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }


    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function subscribes()
    {
        return $this->hasMany(EventSubscribe::class);
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
        if ($this->subscribes()->whereOrganizationId(auth()->id())->exists()) {
            if ($this->subscribes()->whereOrganizationId(auth()->id())->where('active', 0)->exists()) {
                $this->subscribes()->update(['active' => 1]);
                session()->flash('flash', 'Ste prihlásený na akciu!!');
            } else {
                $this->subscribes()->update(['active' => 0]);
                session()->flash('flash', 'Ste odhlásený z akcie!');
            }
        } else {
            $this->subscribes()->create(['organization_id' => auth()->id()]);
            session()->flash('flash', 'Ste prihlásený na akciu!');
        }
    }


    public function isSubscribed()
    {
        return !! $this->subscribes()->where('organization_id', auth()->id())->where('active', 1)->exists();
    }

    public function activeSubscribed()
    {
        return $this->subscribes()->whereActive(1)->count();
    }

    public function getImagethumbAttribute()
    {
        if ($this->images()->whereType('img')->exists()) {
            return "storage/{$this->images()->whereType('img')->first()->thumb}";
        }
        return $this->imagecard;
    }

    public function getImagecardAttribute()
    {
        if ($this->images()->whereType('card')->exists()) {
            return "storage/{$this->images()->whereType('card')->first()->thumb}";
        }
        return asset('images/foto.jpg');
    }
}
