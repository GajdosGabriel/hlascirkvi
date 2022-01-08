<?php

namespace App\Models;

use App\Models\Favoritable;
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

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }
}
