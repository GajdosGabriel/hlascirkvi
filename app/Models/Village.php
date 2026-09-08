<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    // `villages` je číselník naplnený importom a jeho `id` nemá AUTO_INCREMENT.
    // Kým sa Eloquent domnieval opak, po vložení riadku si prečítal lastInsertId()
    // (pri neinkrementovanom kľúči 0) a prepísal ním id v modeli — väzba na
    // novú obec tak ukazovala na neexistujúcu nulu.
    public $incrementing = false;

    public function district() {
        return $this->belongsTo(District::class);
    }
}
