<?php

namespace App\Traits;

use App\Models\Image;
use Storage;


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
            return url($image->ThumbImageUrl);
        }

        if ($this->organization->avatar) {
            return Storage::url('organizations/' . $this->organization->id . '/' . $this->organization->avatar);
        }

        return url('images/foto.jpg');
    }

    public function destroyImages()
    {

        if ($this->images()->exists()) {

            foreach ($this->images as $image) {
                // delete big img
                Storage::delete('public/' . $image->url);

                // delete small img
                Storage::delete('public/' . $image->thumb);

                $image->delete();
            }
        }
    }
}
