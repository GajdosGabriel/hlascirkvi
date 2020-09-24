<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Updater extends Model
{
    protected $fillable = [];
    public $timestamps = false;

    public function organizations() {
        return $this->belongsToMany(Organization::class);
    }
}
