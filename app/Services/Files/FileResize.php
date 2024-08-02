<?php

namespace App\Services\Files;
use Illuminate\Support\Facades\Storage;
use Image;
use App\Enums\ImageSize;


trait FileResize
{

    protected function resizeImage()
    {

        $img = Image::make(Storage::disk('public')->get($this->savedImage->url));
        $img->widen(ImageSize::Large->value)->save(storage_path('app/public/' . $this->savedImage->url));


        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
        $img->widen(ImageSize::Small->value)->save(storage_path('app/public/' . $this->folderPath() . '/thumb/' . basename($this->savedImage->url)));
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
