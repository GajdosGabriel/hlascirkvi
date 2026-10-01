<?php

namespace App\Services\Files;

use App\Services\Images\StoreImage;
use Throwable;

/**
 * Obrázky z formulára článku. Samotné ukladanie robí StoreImage, tu ostáva len
 * to, čo príde z requestu.
 */
class Form
{
    protected $model;
    protected $request;

    /** @var array<int, string> hlásenia o obrázkoch, ktoré sa nepodarilo uložiť */
    protected array $failures = [];

    public function __construct($model, $request, protected bool $videoChanged = false)
    {
        $this->model = $model;
        $this->request = $request;
    }

    /**
     * Chyba jedného obrázka nesmie zhodiť celé uloženie článku — text už je
     * v databáze. Čo sa nepodarilo, vráti sa ako zoznam hlásení.
     *
     * @return array<int, string>
     */
    public function handler(): array
    {
        $this->uploadImages();
        $this->uploadVideoThumbnail();

        StoreImage::ensurePrimary($this->model);

        return $this->failures;
    }

    protected function uploadImages(): void
    {
        foreach ((array) $this->request->file('pictures') as $picture) {
            try {
                StoreImage::for($this->model)->fromUpload($picture);
            } catch (Throwable $e) {
                report($e);

                $this->failures[] = 'Obrázok „' . $picture->getClientOriginalName() . '“ sa nepodarilo uložiť: ' . $e->getMessage();
            }
        }
    }

    /**
     * Náhľad k YouTube videu. Pri zmene videa sa starý náhľad nahradí novým.
     */
    protected function uploadVideoThumbnail(): void
    {
        $videoId = $this->request->input('video_id');

        if (blank($videoId)) {
            return;
        }

        if (! $this->videoChanged && $this->model->images()->exists()) {
            return;
        }

        $previous = $this->videoChanged
            ? $this->model->images()->where('type', 'video')->pluck('id')
            : collect();

        // maxresdefault na starších videách neexistuje a vráti 404,
        // hqdefault je k dispozícii vždy.
        foreach (['maxresdefault', 'hqdefault'] as $variant) {
            $saved = StoreImage::for($this->model)
                ->ofType('video')
                ->tryFromUrl('https://img.youtube.com/vi/' . $videoId . '/' . $variant . '.jpg');

            if ($saved) {
                // Až po úspešnom stiahnutí, aby článok neostal bez náhľadu.
                $this->model->images()->whereKey($previous)->get()->each->delete();

                return;
            }
        }
    }
}
