<?php

namespace App\Services\Canal;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotka z účtu Google (prípadne iného poskytovateľa) ako avatar osobného
 * kanála. Kanál si avatar nesie len ako meno súboru a obrázok číta z disku
 * `public` pod organizations/{id}/ — odkaz na googleusercontent.com by
 * šablóny poskladali na neexistujúcu cestu, preto sa súbor sťahuje.
 *
 * Avatar, ktorý už kanál má, sa neprepisuje: vlastník si ho mohol nastaviť
 * sám a každé prihlásenie by mu ho vrátilo na fotku z Googlu.
 */
class SocialAvatar
{
    public function attach(User $user, ?string $url): void
    {
        if (! $url) {
            return;
        }

        $canal = $user->organizations()
            ->where('person', 1)
            ->where(fn ($query) => $query->whereNull('avatar')->orWhere('avatar', ''))
            ->first();

        if (! $canal) {
            return;
        }

        // Výpadok Googlu ani nečakaná odpoveď nesmú zhodiť prihlásenie —
        // kanál potom len ostane s iniciálkami.
        try {
            $response = Http::timeout(5)->get($this->largerGooglePicture($url));

            $extension = $this->extension((string) $response->header('Content-Type'));

            if (! $response->successful() || ! $extension) {
                return;
            }

            $filename = Str::random(20) . '.' . $extension;

            Storage::disk('public')->put('organizations/' . $canal->id . '/' . $filename, $response->body());

            $canal->update(['avatar' => $filename]);
        } catch (\Throwable $e) {
            Log::warning('Fotku z účtu sa nepodarilo uložiť ako avatar kanála: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'canal_id' => $canal->id,
            ]);
        }
    }

    /**
     * Google posiela štvorec 96 px (…=s96-c), avatar v hlavičke kanála
     * by bol rozmazaný. Veľkosť sa riadi priamo príponou adresy.
     */
    protected function largerGooglePicture(string $url): string
    {
        if (! str_contains($url, 'googleusercontent.com')) {
            return $url;
        }

        return preg_replace('/=s\d+(-c)?$/', '=s400-c', $url);
    }

    protected function extension(string $contentType): ?string
    {
        return match (strtolower(trim(Str::before($contentType, ';')))) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => null,
        };
    }
}
