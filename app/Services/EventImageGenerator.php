<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class EventImageGenerator
{
    protected $model;
    protected $imgFolder;

    public function __construct($model)
    {
        $this->model = $model;
        $this->imgFolder = strtolower(class_basename($this->model)).'s/' . $this->model->organization_id . '/';
    }


    //    Event picture generate
    public function checkIfEvent()
    {
        if(! strtolower(class_basename($this->model)) == 'event') return false;

      $this->imageCreate();
    }


    protected function saveCard($url) {

        // Still only one card in images model
        if($card = $this->model->images()->whereType('card')->first() ) {
             $card->delete();
        }

        $image = $this->model->images()->create([
            'url' => $this->imgFolder . basename($url),
            'name' => $this->model->slug,
            'thumb' => $this->imgFolder . 'thumb/'. basename($url),
            'org_name' => '',
            'mime' => 'jpg',
            'type' => 'card'
        ]);

        return $image;
    }


    public function imageCreate()
    {
        $img = Storage::disk('local')->get('event_background/' .rand(1,3). '.jpg');

        Storage::disk('public')->put( $url = $this->imgFolder .'/' . bin2hex(openssl_random_pseudo_bytes(24)) . '.jpg', $img);

        $image = $this->saveCard($url);

        // create Image from file
        $img = Image::make(storage_path( 'app/public/' . $image->url) );


        // Title od poster
        $img->text( Str::limit($this->model->title, 31) , 600, 80, function($font) {
            $font->file(public_path('font/Oswald-Light.ttf'));
            $font->size(100);
            $font->color('#fdf6e3');
            $font->align('center');
            $font->valign('top');
        });


        // Adresse of action
        $adresse = $this->model->organization->street . ', '. $this->model->village->fullname ;

        $img->text( Str::limit( $adresse, 63) , 600, 280, function($font) {
            $font->file(public_path('font/Oswald-Light.ttf'));
            $font->size(50);
            $font->color('#fdf6e3');
            $font->align('center');
            $font->valign('top');
        });


        // Date action
        $adresse = localized_date('l', $this->model->start_at). ', '.$this->model->start_at->format('d-m-Y');

        $img->text( Str::limit( $adresse, 63) , 600, 410, function($font) {
            $font->file(public_path('font/Oswald-Light.ttf'));
            $font->size(65);
            $font->color('#fdf6e3');
            $font->align('center');
            $font->valign('top');
        });


        // use callback to define details
        $img->text('Pozýva: '. $this->model->organization->title , 600, 550, function($font) {
            $font->file(public_path('font/Oswald-Light.ttf'));
            $font->size(50);
            $font->color('#fdf6e3');
            $font->align('center');
            $font->valign('top');
        });

        $img->widen(1000)->save( storage_path( 'app/public/' . $image->url) );

        Storage::disk('public')->makeDirectory($this->imgFolder . '/thumb');

        $img->widen(280)->save(storage_path( 'app/public/'. $this->imgFolder . '/thumb/'. basename($image->url) ));
    }



}