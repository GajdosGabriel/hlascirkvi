<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSubscribe extends Model
{
    use  SoftDeletes;
    protected $guarded = [];

    public function organization(){
        return $this->belongsTo(Organization::class);
    }
}
