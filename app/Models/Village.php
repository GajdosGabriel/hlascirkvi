<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'fullname', 'shortname', 'zip', 'district_id', 'region_id', 'use'];

    public $timestamps = false;

    // `villages` je číselník naplnený importom a jeho `id` nemá AUTO_INCREMENT.
    // Kým sa Eloquent domnieval opak, po vložení riadku si prečítal lastInsertId()
    // (pri neinkrementovanom kľúči 0) a prepísal ním id v modeli — väzba na
    // novú obec tak ukazovala na neexistujúcu nulu.
    public $incrementing = false;

    /** Záznam „Celé Slovensko" — kanál bez jedného sídla (médiá, celoslovenské diela). */
    public const WHOLE_COUNTRY = 4209;

    /**
     * Obce do výberu vo formulári. „Celé Slovensko" ide na začiatok —
     * abecedne by sa stratilo medzi štyrmi tisíckami obcí.
     */
    public static function forSelect()
    {
        return static::orderByRaw('id = ? desc', [self::WHOLE_COUNTRY])
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'zip']);
    }

    public function district() {
        return $this->belongsTo(District::class);
    }
}
