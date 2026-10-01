<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    /*
     * Trait tu kedysi bol a v tabuľke po ňom ostalo 251 riadkov s vyplneným
     * deleted_at. Bez neho sa zmazané obrázky opäť zobrazovali, $image->delete()
     * mazal riadok natvrdo (súbory ostali na disku) a admin výpis koša padal
     * na onlyTrashed().
     */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'name' => \App\Casts\StringLength255::class,
        'variants' => 'array',
        'is_primary' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getThumbImageUrlAttribute(): string
    {
        return $this->publicUrl($this->thumb);
    }

    public function getOriginalImageUrlAttribute(): string
    {
        return $this->publicUrl($this->url);
    }

    /**
     * Hodnota do atribútu srcset. Staršie záznamy varianty nemajú, vtedy vráti
     * null a šablóna zostane pri obyčajnom src. `$maxWidth` vynechá väčšie
     * varianty (karta v mriežke ich nikdy nepotrebuje), no aspoň jedna ostane.
     */
    public function srcset(string $format = 'jpg', ?int $maxWidth = null): ?string
    {
        $paths = $this->variants[$format] ?? null;

        if (empty($paths)) {
            return null;
        }

        if ($maxWidth !== null) {
            $fits = array_filter($paths, fn ($path, $width) => $width <= $maxWidth, ARRAY_FILTER_USE_BOTH);
            $paths = $fits ?: array_slice($paths, -1, 1, true);
        }

        $sources = [];

        foreach ($paths as $width => $path) {
            $sources[] = $this->publicUrl($path) . ' ' . $width . 'w';
        }

        return implode(', ', $sources);
    }

    protected function publicUrl(?string $path): string
    {
        return \App\Support\MediaUrl::url($path);
    }
}
