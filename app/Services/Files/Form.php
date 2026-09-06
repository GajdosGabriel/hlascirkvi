<?php

namespace App\Services\Files;




use App\Services\Files\File;
use App\Services\Files\FileYoutube;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Laravel\Facades\Image;



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
            // Intervention Image 4 no longer reads remote URLs itself, so fetch the
            // bytes first and decode them from binary.
            $image = Image::decodeBinary(
                Http::get('https://img.youtube.com/vi/' . $this->request->video_id . '/mqdefault.jpg')->throw()->body()
            );

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
