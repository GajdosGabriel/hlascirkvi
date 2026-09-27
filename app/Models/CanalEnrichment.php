<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanalEnrichment extends Model
{
    public function canal()
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
        'recipients' => 'array',
        'notified' => 'array',
    ];
}
