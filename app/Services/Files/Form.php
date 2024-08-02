<?php

namespace App\Services\Files;




use App\Services\Files\File;
use App\Services\Files\FileYoutube;
use Image;



class Form
{

    protected $model;
    protected $request;


    public function __construct($model, $request)
    {
        $this->model = $model;
        $this->request = $request;
    }

    public function handler()
    {
        if ($this->request->pictures) $this->uploadImages();
        if ($this->request->video_id) {
            $image = Image::make('https://img.youtube.com/vi/' . $this->request->video_id . '/mqdefault.jpg');

            (new FileYoutube($this->model, $image))->getVideoPicture();

            $this->model->update(['video_id' => $image]);
        } 
    }

    public function uploadImages()
    {
        if (!$this->request->pictures) return false;

        foreach ($this->request->pictures as $image) {
         (new File($this->model, $image))->uploadImage();
        }
    }
}
