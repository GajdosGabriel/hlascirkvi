<?php

namespace App\Services\Files;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Enums\ImageSize;


trait FileResize
{

    protected function resizeImage()
    {

        $img = Image::decodeBinary(Storage::disk('public')->get($this->savedImage->url));
        $img->scale(width: ImageSize::Large->value)->save(storage_path('app/public/' . $this->savedImage->url));


        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
        $img->scale(width: ImageSize::Small->value)->save(storage_path('app/public/' . $this->folderPath() . '/thumb/' . basename($this->savedImage->url)));
    }

    protected function folderPath()
    {
        return strtolower(class_basename($this->model)) . 's/' . $this->model->organization_id . '/';
    }



    protected function createDirectory()
    {
        Storage::disk('public')->makeDirectory($this->folderPath());
        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
    }


    
}
