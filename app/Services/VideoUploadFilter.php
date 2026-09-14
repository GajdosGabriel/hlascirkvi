<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\Models\Canal;



class VideoUploadFilter
{
    public $organization;
    public $title;

    // Preklep v názve (má byť countWords), ale property sa pod ním číta na
    // troch miestach v tejto triede — premenovanie patrí k väčšiemu upratovaniu.
    // Deklarácia tu je preto, že dynamické vlastnosti sú v PHP 9 fatal.
    public bool $coutWords = false;

    public function __construct(Canal $organization, $title)
    {
        $this->organization = $organization;
        $this->title = $title;
        $this->coutWords = false;
    }

    public function wordsChecker()
    {
        $this->countWords();

        if ($this->organization->id === 256 ) {
            return ! $this->coutWords;
        }
        return $this->coutWords;
    }

    public function getAcceptedWords()
    {
        // Kresťanské spoločenstvo
        if ($this->organization->id === 256) {
            return [
                'Bohoslužba Banská Bystrica',
            ];
        }
        return [];
    }


    public function countWords()
    {
        $title = mb_strtolower((string) $this->title);

        foreach ($this->getAcceptedWords() as $word) {
            // Argumenty strpos boli prehodené — hľadalo sa, či kľúčové slovo
            // obsahuje celý titulok. Kanál 256 tak od 01/2022 neprepustil ani
            // jedno video.
            if (str_contains($title, mb_strtolower($word))) {
                $this->coutWords = true;
            }
        }
    }


    public function getExcusedWords()
    {
        //
    }
}
