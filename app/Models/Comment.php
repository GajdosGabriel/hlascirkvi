<?php

namespace App\Models;


use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasDatetime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use SoftDeletes, HasFactory, HasFavorites, HasFilter, HasDatetime;

    protected $guarded= [];
    protected $hidden = ['commentable_type', 'updated_at', 'deleted_at'];

    protected $with = ['favorites', 'organization'];
    protected $appends = ['favoritesCount', 'isFavorited', 'datetime'];


    public function organization() {

        return $this->belongsTo(Organization::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function setBodyAttribute($value)
    // {
    //     $this->attributes['body'] = cleanHardSpace($value);
    // }

}
