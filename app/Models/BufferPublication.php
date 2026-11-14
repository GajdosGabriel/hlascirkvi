<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jeden riadok = jeden príspevok, ktorý pustil buffer publisher.
 */
class BufferPublication extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'slot_at' => 'datetime',
        'arrived_at' => 'datetime',
        'archive' => 'boolean',
    ];

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** @return BelongsTo<Canal, $this> */
    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereBetween('slot_at', [
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
        ]);
    }
}
