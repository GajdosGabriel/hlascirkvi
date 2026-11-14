<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verse extends Model
{
    public $timestamps = false;

    /**
     * Zamyslenie na dnešný deň. Tabuľka má 365 riadkov a ID je poradie dňa
     * v roku — 31. decembra priestupného roka (deň 366) by preto nenašlo nič
     * a /zamyslenia by vrátili 404. Posledný deň si vtedy zopakuje posledné
     * zamyslenie.
     */
    public static function today(): ?self
    {
        return static::where('id', '<=', now()->dayOfYear)->orderByDesc('id')->first();
    }
}
