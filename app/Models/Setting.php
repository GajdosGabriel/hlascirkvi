<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Nastavenie prepínané z administrácie. Hodnota je text; typ si určuje
 * volajúci. Číta sa pri každej požiadavke, preto cez cache.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $value = Cache::rememberForever('setting:' . $key, fn () => static::query()->whereKey($key)->value('value'));

        return $value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);

        Cache::forget('setting:' . $key);
    }
}
