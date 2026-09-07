<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereBetween('slot_at', [
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
        ]);
    }
}
