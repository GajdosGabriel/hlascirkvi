<?php

namespace App\Models;



use App\Traits\HasComments;
use App\Traits\HasDatetime;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prayer extends Model
{
    use Notifiable, HasFactory, SoftDeletes, HasFavorites , HasComments, HasOrganization, HasFilter, HasDatetime;

    protected $casts = ['title' => \App\Casts\StringLength255::class];
    protected $guarded = ['id'];
    protected $appends = ['favoritesCount', 'isFavorited'];
    protected $with = ['favorites'];


}
