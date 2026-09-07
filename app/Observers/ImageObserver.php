<?php

namespace App\Observers;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    /**
     * Súbory sa mažú až pri definitívnom zmazaní – kým je záznam len v koši,
     * musí sa dať vrátiť. Doteraz úklid nikde nebol a po zmazaní obrázka
     * ostávali varianty na disku ako siroty.
     */
    public function forceDeleted(Image $image): void
    {
        $paths = [$image->url, $image->thumb];

        foreach (($image->variants ?? []) as $byWidth) {
            foreach ($byWidth as $path) {
                $paths[] = $path;
            }
        }

        $paths = array_values(array_filter(array_unique($paths)));

        if ($paths !== []) {
            Storage::disk(config('images.disk'))->delete($paths);
        }
    }
}
