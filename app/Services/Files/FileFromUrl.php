<?php

namespace App\Services\Files;


use Image;
use App\Services\Files\FileName;
use App\Services\Files\FileExtention;


class FileFromUrl
{
    use FileName, FileExtention, FileResize;


    protected $model;
    protected $url;
    protected $savedImage;

    public function __construct($model, $url)
    {
        $this->model = $model;
        $this->url = $url;
        $this->makePictureFromUrl();
    }

    // Create img from event/ POZVANKY
    public function makePictureFromUrl()
    {
        $file_name = $this->model->slug . '-' . rand(1000, 90000) . '.jpg';
        //        $file_name = bin2hex(openssl_random_pseudo_bytes(24)) . '.jpg';

        $this->createDirectory();

        $img = Image::make($this->url);

        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/' . $file_name, $img));
        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/thumb/' . $file_name, $img));

        $this->savedImage = $this->model->images()->create([
            'name' => $this->generateUniqueName(),
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
