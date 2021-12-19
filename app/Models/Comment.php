<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use Favoritable, SoftDeletes;

    protected $guarded= [];
    protected $hidden = ['commentable_id', 'commentable_type', 'updated_at', 'deleted_at'];

    protected $with = ['favorites'];
    protected $appends = ['favoritesCount', 'isFavorited'];


    public function user() {

        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

}
