<?php

namespace App\Models;

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
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'name' => \App\Casts\StringLength255::class,
        'variants' => 'array',
        'is_primary' => 'boolean',
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
     * null a šablóna zostane pri obyčajnom src.
     */
    public function srcset(string $format = 'jpg'): ?string
    {
        $paths = $this->variants[$format] ?? null;

        if (empty($paths)) {
            return null;
        }

        $sources = [];

        foreach ($paths as $width => $path) {
            $sources[] = $this->publicUrl($path) . ' ' . $width . 'w';
        }

        return implode(', ', $sources);
    }

    /**
     * Všetky cesty na disku sú relatívne k disku, adresu z nich skladáme na
     * jednom mieste. Lokálny vývoj si ju berie z produkcie – predtým to bola
     * podmienka priamo v accessore náhľadu, takže veľký obrázok sa lokálne
     * nenačítal a šablóna to musela riešiť onerror fallbackom.
     */
    protected function publicUrl(?string $path): string
    {
        $path = ltrim((string) $path, '/');
        $disk = Storage::disk(config('images.disk'));

        // Z produkcie sa dotiahne len to, čo v úložisku naozaj chýba, takže
        // obrázky nahraté lokálne sa dajú lokálne aj pozrieť. Na produkcii je
        // remote_base prázdne a k dopytu na disk sa vôbec nedôjde.
        if (($base = config('images.remote_base')) && ! $disk->exists($path)) {
            return rtrim($base, '/') . '/' . $path;
        }

        return $disk->url($path);
    }
}
