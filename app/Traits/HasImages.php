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

    /**
     * Atribút je v $appends, takže sa počíta pri každej serializácii.
     *
     * Ak väzba `images` načítaná nie je, pýtame si z databázy jeden riadok
     * (`images()->first()`), nie celú kolekciu obrázkov príspevku len preto,
     * aby sme z nej vzali prvý.
     */
    public function getThumbImageAttribute()
    {
        $image = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        if ($image) {
            return url($image->thumbImageUrl);
        }

        // Kanál sa dotiahne aj keď načítaný nie je — bez neho by sa namiesto
        // jeho avatara ticho zobrazil zástupný obrázok.
        $organization = $this->organization;

        if ($organization && $organization->avatar) {
            return Storage::url('organizations/' . $organization->id . '/' . $organization->avatar);
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
