<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Záznam o rozoslaní mesačného newslettera, jeden na mesiac (`period` = Y-m). */
class NewsletterRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'finished_at' => 'datetime',
    ];
}
