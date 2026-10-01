<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Jediné miesto, kde sa z cesty na disku skladá verejná adresa obrázka.
 * Všetko ide cez disk `images.disk` (lokálne public, na ostrom S3), takže
 * šablóny nesmú siahať na `url('storage/…')` ani na predvolený disk.
 */
class MediaUrl
{
    public const DEFAULT_CANAL = 'images/avatar.png';
    public const DEFAULT_USER = 'images/avatar.png';
    public const DEFAULT_POST = 'images/foto.jpg';

    public static function disk(): Filesystem
    {
        return Storage::disk(config('images.disk'));
    }

    public static function url(?string $path): string
    {
        $path = ltrim((string) $path, '/');
        $disk = static::disk();

        // Z produkcie sa dotiahne len to, čo v úložisku naozaj chýba. Na
        // produkcii je remote_base prázdne a k dopytu na disk sa nedôjde.
        if (($base = config('images.remote_base')) && ! $disk->exists($path)) {
            return rtrim($base, '/') . '/' . $path;
        }

        return $disk->url($path);
    }

    public static function canalAvatar(int|string|null $canalId, ?string $file): string
    {
        return $canalId && $file
            ? static::url('organizations/' . $canalId . '/' . $file)
            : asset(static::DEFAULT_CANAL);
    }

    public static function userAvatar(int|string|null $userId, ?string $file): string
    {
        return $userId && $file
            ? static::url('users/' . $userId . '/' . $file)
            : asset(static::DEFAULT_USER);
    }
}
