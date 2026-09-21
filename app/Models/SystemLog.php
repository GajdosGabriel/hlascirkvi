<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Záznam v denníku udalostí. Zapisuje sa cez App\Services\SystemLog\Recorder,
 * nie priamo — ten sa postará, aby zlyhanie zápisu nezhodilo request.
 */
class SystemLog extends Model
{
    use HasFilter, MassPrunable;

    public const UPDATED_AT = null;

    public const LEVELS = ['info', 'warning', 'error'];

    public const STATUSES = ['sent', 'failed', 'skipped', 'ok'];

    protected $guarded = ['id'];

    protected $casts = [
        'context' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /** Info po mesiaci, varovania a chyby po troch (config/logging.php). */
    public function prunable()
    {
        $days = max(1, (int) config('logging.system_log.days', 31));
        $errorDays = max($days, (int) config('logging.system_log.error_days', 90));

        return static::query()
            ->where('created_at', '<', now()->subDays($errorDays))
            ->orWhere(fn ($query) => $query
                ->where('level', 'info')
                ->where('created_at', '<', now()->subDays($days)));
    }
}
