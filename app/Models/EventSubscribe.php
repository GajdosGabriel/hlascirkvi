<?php

namespace App\Models;

use App\Traits\HasFilter;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSubscribe extends Model
{
    use  SoftDeletes, HasOrganization, HasFilter;
    protected $guarded = [];


    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
