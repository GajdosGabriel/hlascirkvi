<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /** @return HasMany<Village, $this> */
    public function villages(): HasMany {
        return $this->hasMany(Village::class);
    }
}
