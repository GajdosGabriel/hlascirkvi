<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanalEnrichment extends Model
{
    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class);
    }

    protected $guarded = ['id'];

    protected $casts = [
        'retry_at' => 'datetime',
        'completed_at' => 'datetime',
        'notification_completed_at' => 'datetime',
        'changes' => 'array',
        'evidence' => 'array',
        'diagnostics' => 'array',
        'recipients' => 'array',
        'notified' => 'array',
    ];
}
