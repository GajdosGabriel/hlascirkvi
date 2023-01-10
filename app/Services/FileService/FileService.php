<?php

namespace App\Services\FileService;

use Illuminate\Support\Facades\Storage;
use Image;


class FileService
{
    protected $model;
    protected $image;

    public function store($model, $request)
    {

        $this->model = $model;

        if (!$request->pictures) return false;

        foreach ($request->pictures as $image) {

            $url = Storage::disk('public')->put("posts/" , $image);

            // $model->slug . '-' . rand(1000, 90000) . '.' .  $image->extension();
            // $url = $image->storeAs($this->folderPath($model), $file_name, 'public');

            $this->image = $model->images()->create([
                'url' => $url,
                'name' => $model->slug,
                'thumb' => $this->folderPath() . 'thumb/' . basename($url),
                'org_name' => $image->getClientOriginalName(),
                'size' => $image->getClientOriginalExtension(),
                'mime' => $image->extension()
            ]);

            $this->resizePostImage();
        }
    }



    protected function resizePostImage()
    {
        $big = 1000;
        $thumb = 280;

        $image = array('jpg', 'jpeg', 'gif', 'png');
        if (!in_array(strtolower($this->image->mime), $image)) return;


        $this->image->update(['type' => 'img']);

        $img = Image::make(Storage::disk('public')->get($this->image->url));
        $img->widen($big)->save(storage_path('app/public/' . $this->image->url));


        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
        $img->widen($thumb)->save(storage_path('app/public/' . $this->folderPath() . '/thumb/' . basename($this->image->url)));
    }

    protected function createDirectory()
    {
        Storage::disk('public')->makeDirectory($this->folderPath());
        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
    }

    protected function folderPath()
    {
        return strtolower(class_basename($this->model)) . 's/' . $this->model->organization_id . '/';
    }
}
