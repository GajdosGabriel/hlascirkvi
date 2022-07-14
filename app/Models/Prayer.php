<?php

namespace App\Models;

use App\Models\Favoritable;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prayer extends Model
{
    use Notifiable, HasFactory, SoftDeletes, Favoritable;

    protected $guarded = ['id'];
    protected $appends = ['favoritesCount', 'isFavorited'];
    protected $with = ['favorites'];

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function comments() {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

}
