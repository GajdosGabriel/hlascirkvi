<?php

namespace App\Services\Files;

use App\Services\Images\StoreImage;

/**
 * Obrázky z formulára článku. Samotné ukladanie robí StoreImage, tu ostáva len
 * to, čo príde z requestu.
 */
class Form
{
    protected $model;
    protected $request;

    public function __construct($model, $request)
    {
        $this->model = $model;
        $this->request = $request;
    }

    public function handler(): void
    {
        $this->uploadImages();
        $this->uploadVideoThumbnail();
    }

    protected function uploadImages(): void
    {
        foreach ((array) $this->request->file('pictures') as $picture) {
            StoreImage::for($this->model)->fromUpload($picture);
        }
    }

    /**
     * Náhľad k YouTube videu. Pôvodná verzia sem zapisovala objekt
     * Intervention\Image do stĺpca video_id (a do org_name), čo končilo
     * fatálnou chybou pri každom uložení článku s videom.
     */
    protected function uploadVideoThumbnail(): void
    {
        $videoId = $this->request->input('video_id');

        if (blank($videoId) || $this->model->images()->exists()) {
            return;
        }

        // maxresdefault na starších videách neexistuje a vráti 404,
        // hqdefault je k dispozícii vždy.
        foreach (['maxresdefault', 'hqdefault'] as $variant) {
            $saved = StoreImage::for($this->model)
                ->tryFromUrl('https://img.youtube.com/vi/' . $videoId . '/' . $variant . '.jpg');

            if ($saved) {
                return;
            }
        }
    }
}
