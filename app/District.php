<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function villages() {
        return $this->hasMany(Village::class);
    }
}
