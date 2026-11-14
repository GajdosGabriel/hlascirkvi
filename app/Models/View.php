<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Jedno zobrazenie príspevku. Zapisuje sa hromadne cez query builder
 * (App\Services\VisitModels\ViewRecorder), model slúži na čítanie a testy.
 */
class View extends Model
{
    protected $fillable = ['viewable_type', 'viewable_id', 'visitor_hash', 'viewed_on', 'created_at'];

    // Tabuľka má len created_at, žiadne updated_at — riadok sa nikdy needituje.
    public $timestamps = false;

    protected $casts = [
        'viewed_on' => 'date',
        'created_at' => 'datetime',
    ];

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, $this> */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }
}
