<?php

namespace App\Services\Files;


use Image;
use App\Services\Files\FileName;
use App\Services\Files\FileExtention;


class FilePozvanky
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

    // Create img from event/ POZVANKY
    public function getPictureFromEvent()
    {
        $file_name = $this->model->slug . '-' . rand(1000, 90000) . '.jpg';
        //        $file_name = bin2hex(openssl_random_pseudo_bytes(24)) . '.jpg';

        $this->createDirectory();

        $img = Image::make($this->image);

        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/' . $file_name, $img));
        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/thumb/' . $file_name, $img));

        $this->image = $this->model->images()->create([
            'name' => $this->getName($this->image),
            'url' => $this->folderPath() . basename($url),
            'thumb' => $this->folderPath() . 'thumb/' . basename($url),
            'org_name' => '',
            'size' => 0,
            'mime' => 'jpg',
            'type' => 'img'
        ]);

        $this->resizeImage();


        //        $this->model->update(['video_id' => $videoId]);

    }

}
