<?php

namespace App\Traits;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

trait HasImages
{
    public function images()
    {
        return $this->morphMany(Image::class, 'fileable');
    }

    public function getThumbImageAttribute()
    {
        $image = $this->images->first();

        if ($image) {
            return url($image->thumbImageUrl);
        }

        if ($this->organization->avatar) {
            return Storage::url('organizations/' . $this->organization->id . '/' . $this->organization->avatar);
        }

        return url('images/foto.jpg');
    }

    /**
     * Volá sa pri definitívnom zmazaní modelu. Samotné súbory upratuje
     * ImageObserver, aby bol úklid na jednom mieste aj pri mazaní z admina.
     */
    public function destroyImages(): void
    {
        $this->images()->withTrashed()->get()->each->forceDelete();
    }
}
