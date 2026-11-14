<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Verse extends Model
{
    public $timestamps = false;

    /**
     * Referenčný nepriestupný rok. Tabuľka má 365 riadkov a ID je poradové
     * číslo dňa v takomto roku (1 = 1. január, 60 = 1. marec, 365 = 31. december).
     */
    private const REFERENCE_YEAR = 2025;

    /**
     * ID zamyslenia pre kalendárny dátum (mesiac a deň). Predtým sa bral
     * dayOfYear daného roka, takže v priestupnom roku sa od 29. 2. všetko
     * posunulo o deň. 29. február zopakuje zamyslenie 28. februára.
     */
    public static function dayNumberFor(CarbonInterface $date): int
    {
        $day = ($date->month === 2 && $date->day === 29) ? 28 : $date->day;

        return Carbon::create(self::REFERENCE_YEAR, $date->month, $day)->dayOfYear;
    }

    /**
     * Kalendárny dátum zamyslenia v danom roku — opak dayNumberFor().
     */
    public static function dateFor(int $id, ?int $year = null): Carbon
    {
        $reference = Carbon::create(self::REFERENCE_YEAR, 1, 1)->addDays($id - 1);

        return Carbon::create($year ?? now()->year, $reference->month, $reference->day);
    }

    /**
     * Zamyslenie na daný deň. Ak by riadok chýbal (diera v ID), vezme sa
     * najbližšie predchádzajúce, aby /zamyslenia nevrátili 404.
     */
    public static function forDate(CarbonInterface $date): ?self
    {
        return static::where('id', '<=', static::dayNumberFor($date))->orderByDesc('id')->first();
    }

    public static function today(): ?self
    {
        return static::forDate(now());
    }
}
