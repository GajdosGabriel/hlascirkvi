<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Urlwww implements CastsAttributes
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
    public function get($model, string $key, $value, array $attributes): ?string
    {
        return $value;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        return $this->normalizeUrl($value);
    }

    protected function normalizeUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Doplní https:// ak chýba
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parsed = parse_url($url);
        if (empty($parsed['host'])) {
            return null;
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];

        return $scheme . '://' . $host;
    }
}
