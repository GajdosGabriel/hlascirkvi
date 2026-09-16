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

    // Tabuľka `comments` nemá stĺpec canal_id, takže eager load väzby na
    // kanál len posielal dopyt bez kľúčov. Komentár patrí užívateľovi —
    // toho načítavajú výpisy cez ->with('user').
    protected $with = ['favorites'];
    protected $appends = ['favoritesCount', 'isFavorited', 'datetime'];


    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Vlákno má jednu úroveň, preto sa odpovede ďalej nevnárajú.
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user')->oldest();
    }

    /**
     * Komentár stiahnutý z YouTube (App\Services\Youtube\CommentSync). Staršie
     * záznamy ID nemajú, spoznajú sa podľa avatara z YouTube.
     */
    public function fromYoutube(): bool
    {
        return $this->youtube_comment_id !== null
            || ((int) $this->user_id === \App\Services\Youtube\CommentSync::USER_ID
                && str_starts_with((string) $this->user_avatar, 'https://yt3.'));
    }

    // public function setBodyAttribute($value)
    // {
    //     $this->attributes['body'] = cleanHardSpace($value);
    // }

}
