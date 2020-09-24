<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddresBook extends Model
{
    use SoftDeletes;
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('meContact', function (Builder $builder) {
            $builder->whereUserId(auth()->id());
        });

    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getFullnameAttribute()
    {
        return $this->last_name . ' '. $this->first_name;
    }




}
