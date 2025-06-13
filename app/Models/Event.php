<?php

namespace App\Models;

use Carbon\Carbon;

use App\Traits\HasRoute;
use App\Traits\HasFilter;
use App\Traits\HasImages;
use App\Traits\HasComments;
use App\Traits\HasDatetime;
use Illuminate\Support\Str;
use App\Traits\HasFavorites;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model implements Viewable
{
    use SoftDeletes, InteractsWithViews, HasFactory, HasFavorites, HasComments, HasImages, HasOrganization, HasRoute, HasFilter, HasDatetime;
    protected $guarded = ['id'];
    protected $casts = ['title' => \App\Casts\StringLength255::class];
    protected $appends = [];



    //    protected $with = ['village', 'organization'];

    //    protected $casts = [
    //        'start_at'  => 'datetime:Y-m-d H:i',
    //        'end_at'    => 'datetime:Y-m-d H:i',
    //    ];

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
        if ($subscribeExist = $this->subscribes()->withTrashed()->whereOrganizationId(auth()->id())->first()) {

            if ($subscribeExist->active == 0) {
                $this->subscribes()->update(['active' => 1]);
                session()->flash('flash', 'Ste prihlásený na akciu!!');
            } else {
                $this->subscribes()->update(['active' => 0]);
                session()->flash('flash', 'Odhlásili ste sa z akcie!');
            }
            return $subscribeExist;
        } else {
            return  $this->subscribes()->create(['organization_id' => auth()->id()]);
            session()->flash('flash', 'Ste prihlásený na akciu!');
        }
    }


    public function isSubscribed()
    {
        return !!$this->subscribes()->where('organization_id', auth()->id())->where('active', 1)->exists();
    }

    public function activeSubscribed()
    {
        return $this->subscribes()->whereActive(1)->count();
    }

    public function getImagethumbAttribute()
    {
        if ($image = $this->images()->whereType('img')->first()) {
            return "storage/{$image->thumb}";
        }
        return $this->imagecard;
    }

    public function getImagecardAttribute()
    {
        if ($image = $this->images()->whereType('card')->first()) {
            return "storage/{$image->thumb}";
        }
        return asset('images/foto.jpg');
    }

    public function scopeUnpublished()
    {
        return $this->wherePublished(null);
    }


   


    // public function url()
    // {
    //     $url = [];
    //     if (auth()->user()->can('view', $this)) {
    //         array_push($url, [ 'show'     =>  route('admin.event.show', $this->id)]);
    //     }
    //     if (auth()->user()->can('create', $this)) {
    //         array_push($url, [ 'create'     =>  route('admin.event.create')]);
    //     } 
    //     if (auth()->user()->can('update', $this)) {
    //         array_push($url, [ 'edit'     =>  route('admin.event.update', $this->id)]);
    //     }
    //     if (auth()->user()->can('delete', $this)) {
    //         array_push($url, [ 'delete'     =>  route('admin.event.destroy', $this->id)]);
    //     }
    //     return $url;
    // }
}
