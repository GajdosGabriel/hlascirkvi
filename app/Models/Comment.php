<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use Favoritable, SoftDeletes, HasFactory;

    protected $guarded= [];
    protected $hidden = ['commentable_type', 'updated_at', 'deleted_at'];

    protected $with = ['favorites', 'user'];
    protected $appends = ['favoritesCount', 'isFavorited'];


    public function user() {

        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }
    

    // public function setBodyAttribute($value)
    // {
    //     $this->attributes['body'] = cleanHardSpace($value);
    // }

}
