<?php

namespace App\Services\Files;


use App\Services\Files\FileName;
use App\Services\Files\FileExtention;


class File
{
    use FileName, FileExtention, FileResize;


    protected $model;
    protected $image;
    protected $savedImage;

    public function __construct($model, $image)
    {
        $this->model = $model;
        $this->image = $image;
    }


    public function uploadImage()
    {
        $url = $this->image->storeAs($this->folderPath(), $this->generateUniqueName(), 'public');

        $this->savedImage = $this->model->images()->create([
            'url' => $this->folderPath() . basename($url),
            'name' => $this->getName($this->image),
            'thumb' => $this->folderPath() . 'thumb/' . basename($url),
            'org_name' => $this->getOrginalName($this->image),
            'size' => $this->image->getClientOriginalExtension(),
            'mime' => $this->getExtention($this->image)
        ]);

        $this->resizeImage();
    }

}
