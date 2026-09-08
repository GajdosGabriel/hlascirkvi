<?php

namespace App\Casts;

use DateInterval;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class VideoDuration implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (! $value) {
            return $value;
        }

        try {
            $duration = new DateInterval($value);
        } catch (\Exception $e) {
            // Neplatná hodnota v stĺpci zhodila serializáciu celej stránky.
            return null;
        }

        // Hodiny sa zahadzovali (riadok bol zakomentovaný), takže PT1H30M
        // sa zobrazilo ako 30:00. date('s', ...) bolo navyše zneužitie funkcie
        // na doplnenie nuly — fungovalo len preto, že sekundy sú vždy 0-59.
        $hours = $duration->h + $duration->d * 24;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $duration->i, $duration->s);
        }

        return sprintf('%d:%02d', $duration->i, $duration->s);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
