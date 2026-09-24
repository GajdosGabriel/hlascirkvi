<?php

namespace App\Models;



use App\Traits\HasComments;
use App\Traits\HasDatetime;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasCanal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prayer extends Model
{
    use Notifiable, HasFactory, SoftDeletes, HasFavorites , HasComments, HasCanal, HasFilter, HasDatetime;

    protected $casts = [
        'title' => \App\Casts\StringLength255::class,
        'published' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! array_key_exists('published', $model->getAttributes())) {
                $model->published = now();
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull($this->qualifyColumn('published'));
    }

    protected $guarded = ['id'];
    protected $appends = ['favoritesCount', 'isFavorited'];
    protected $with = ['favorites'];


}
