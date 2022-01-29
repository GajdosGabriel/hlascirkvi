<?php

namespace App\Services;


use Illuminate\Support\Facades\Storage;
use Image;

use Illuminate\Http\File;


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
        if ($this->request->picture) $this->uploadImages();
        if ($this->request->video_id) $this->getVideoPicture();
    }

    public function uploadImages()
    {

        if (!$this->request->picture) return false;

        foreach ($this->request->picture as $image) {
            $file_name = $this->model->slug . '-' . rand(1000, 90000) . '.' .  $image->extension();
            $url = $image->storeAs($this->folderPath(), $file_name, 'public');

            $this->image = $this->model->images()->create([
                'url' => $this->folderPath() . basename($url),
                'name' => $this->model->slug,
                'thumb' => $this->folderPath() . 'thumb/' . basename($url),
                'org_name' => $image->getClientOriginalName(),
                'size' => $image->getClientOriginalExtension(),
                'mime' => $image->extension()
            ]);



            $this->resizePostImage($big = 1000, $thumb = 280);
        }
    }

    protected function folderPath()
    {
        return strtolower(class_basename($this->model)) . 's/' . $this->model->organization_id . '/';
    }

    protected function resizePostImage($big, $thumb)
    {

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


    // Create img from youtube video, use in z článok create
    public function getVideoPicture()
    {
        if (!$this->request->video_id or strlen($this->request->video_id) < 12) return false;

        $videoId = $this->getYouTubeIdFromURL();

        $file_name = $this->model->slug . '-' . rand(1000, 90000) . '.jpg';
        //        $file_name = bin2hex(openssl_random_pseudo_bytes(24)) . '.jpg';


        $this->createDirectory();

        $img = Image::make('https://img.youtube.com/vi/' . $videoId . '/mqdefault.jpg');

        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/' . $file_name, $img));
        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/thumb/' . $file_name, $img));

        $this->image = $this->model->images()->create([
            'name' => $this->model->slug,
            'url' => $this->folderPath() . basename($url),
            'thumb' => $this->folderPath() . 'thumb/' . basename($url),
            'org_name' => '',
            'size' => 0,
            'mime' => 'jpg'
        ]);

        $this->model->update(['video_id' => $videoId]);
    }



    // Create img from event/ POZVANKY
    public function getPictureFromEvent()
    {
        $file_name = $this->model->slug . '-' . rand(1000, 90000) . '.jpg';
        //        $file_name = bin2hex(openssl_random_pseudo_bytes(24)) . '.jpg';

        $this->createDirectory();

        $img = Image::make($this->request);

        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/' . $file_name, $img));
        $img->save($url = storage_path('app/public/' . $this->folderPath() . '/thumb/' . $file_name, $img));

        $this->image = $this->model->images()->create([
            'name' => $this->model->slug,
            'url' => $this->folderPath() . basename($url),
            'thumb' => $this->folderPath() . 'thumb/' . basename($url),
            'org_name' => '',
            'size' => 0,
            'mime' => 'jpg',
            'type' => 'img'
        ]);

        $this->resizePostImage($big = 800, $thumb = 180);


        //        $this->model->update(['video_id' => $videoId]);

    }



    //Vybrat ID video z Youtube
    function getYouTubeIdFromURL()
    {
        $url_string = parse_url(request('video_id'), PHP_URL_QUERY);
        parse_str($url_string, $args);
        return isset($args['v']) ? $args['v'] : false;
    }
}
