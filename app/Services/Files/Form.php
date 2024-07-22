<?php

namespace App\Services\Files;


use Illuminate\Support\Facades\Storage;
use Image;
use App\Enums\ImageSize;


class Form
{

    protected $model;
    protected $request;
    protected $image;

    public function __construct($model, $request, $image = null)
    {
        $this->model = $model;
        $this->request = $request;
        $this->image = $image;
    }

    public function handler()
    {
        if ($this->request->pictures) $this->uploadImages();
        if ($this->request->video_id) $this->getVideoPicture();
    }

    public function uploadImages()
    {

        if (!$this->request->pictures) return false;

        foreach ($this->request->pictures as $image) {
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

            $this->resizePostImage();
        }
    }

    protected function folderPath()
    {
        return strtolower(class_basename($this->model)) . 's/' . $this->model->organization_id . '/';
    }

    protected function resizePostImage()
    {

        $image = array('jpg', 'jpeg', 'gif', 'png');
        if (!in_array(strtolower($this->image->mime), $image)) return;


        $this->image->update(['type' => 'img']);

        $img = Image::make(Storage::disk('public')->get($this->image->url));
        $img->widen(ImageSize::Large->value)->save(storage_path('app/public/' . $this->image->url));


        Storage::disk('public')->makeDirectory($this->folderPath() . '/thumb');
        $img->widen(ImageSize::Small->value)->save(storage_path('app/public/' . $this->folderPath() . '/thumb/' . basename($this->image->url)));
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

        $this->resizePostImage();


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
