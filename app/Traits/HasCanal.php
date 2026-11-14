<?php

namespace App\Traits;

use App\Models\Canal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


trait HasCanal
{

    /** @return BelongsTo<Canal, $this> */
    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class);
    }

    
}