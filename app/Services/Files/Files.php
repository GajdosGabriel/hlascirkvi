<?php

namespace App\Services\Files;

use Illuminate\Support\Facades\Storage;
use Image;


class Files
{
    protected $model;
    protected $images;


    public function __construct($model, $images)
    {
        $this->model = $model;
        $this->images = $images;
    }

    public function store()
    {


        if (!$this->images->pictures) return;

        $this->createDirectory();

        foreach ($this->images->pictures as $image) {

            $url = Storage::disk('public')->put($this->folderPath(), $image);

            $this->image = $this->model->images()->create([
                'url' => $url,
                'name' => $this->model->slug,
                'thumb' => $this->folderPath() . 'thumb/' . basename($url),
                'org_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime' => $image->extension()
            ]);

            $this->resizePostImage();
        }
    }


    public function destroy($image)
    {
        Storage::disk('public')->delete($image->url);
        Storage::disk('public')->delete($image->thumb);
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
