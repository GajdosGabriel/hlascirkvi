<?php

namespace App\Services\Files;



use App\Services\Files\FileName;
use App\Services\Files\FileExtention;


class FileYoutube
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


    // Create img from youtube video, from Post form create
    public function getVideoPicture()
    {
        // if (!$this->image or strlen($this->image) < 12) return false;

        // $image = $this->getYouTubeIdFromURL();


        $this->createDirectory();

       

        $this->image->save($url = storage_path('app/public/' . $this->folderPath() . '/' . $this->getName() . '.jpg' , $this->image));
        $this->image->save($url = storage_path('app/public/' . $this->folderPath() . '/thumb/' . $this->getName() . '.jpg', $this->image));

        $this->savedImage = $this->model->images()->create([
            'name' => $this->getName(),
            'url' => $this->folderPath() . basename($url),
            'thumb' => $this->folderPath() . 'thumb/' . basename($url),
            'org_name' => $this->image,
            'size' => 0,
            'mime' => 'jpg'
        ]);
    }


    //Vybrat ID video z Youtube
    function getYouTubeIdFromURL()
    {
        $url_string = parse_url(request('video_id'), PHP_URL_QUERY);
        parse_str($url_string, $args);
        return isset($args['v']) ? $args['v'] : false;
    }
}
