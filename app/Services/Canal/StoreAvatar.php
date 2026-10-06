<?php

namespace App\Services\Canal;

use App\Models\Canal;
use App\Support\MediaUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class StoreAvatar
{
    public function save(Canal $canal, ?UploadedFile $file, bool $remove = false): void
    {
        $previous = $canal->avatar;
        $path = null;

        if ($file) {
            try {
                $image = Image::decodeBinary($file->getContent());
                $image->orient();
                $image->scaleDown(width: 512, height: 512);
                $filename = Str::uuid() . '.webp';
                $path = 'organizations/' . $canal->id . '/' . $filename;
                if (! MediaUrl::disk()->put($path, (string) $image->encode(new WebpEncoder(quality: 80)))) {
                    throw new \RuntimeException('Avatar storage failed.');
                }
                $canal->avatar = $filename;
            } catch (Throwable $e) {
                if ($path) {
                    $this->delete($path);
                }
                report($e);
                throw ValidationException::withMessages(['avatar_file' => 'Fotku sa nepodarilo uložiť. Skúste to znova.']);
            }
        } elseif ($remove) {
            $canal->avatar = null;
        }

        try {
            $canal->save();
        } catch (Throwable $e) {
            if ($path) {
                $this->delete($path);
            }
            throw $e;
        }

        if ($previous && $previous !== $canal->avatar) {
            $this->delete('organizations/' . $canal->id . '/' . $previous);
        }
    }

    private function delete(string $path): void
    {
        try {
            if (! MediaUrl::disk()->delete($path)) {
                Log::warning('Starý avatar sa nepodarilo odstrániť.', ['path' => $path]);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
