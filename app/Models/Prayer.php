<?php

namespace App\Models;

use App\Models\Favoritable;
use App\Models\Organization;
use App\Traits\HasComments;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prayer extends Model
{
    use Notifiable, HasFactory, SoftDeletes, Favoritable, HasComments, HasOrganization;

    protected $guarded = ['id'];
    protected $appends = ['favoritesCount', 'isFavorited'];
    protected $with = ['favorites'];

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

}
