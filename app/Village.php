<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function district() {
        return $this->belongsTo(District::class);
    }
}
