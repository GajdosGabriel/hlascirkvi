<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\Models\Organization;



class VideoUploadFilter
{
    public $organization;
    public $title;


    public function __construct(Organization $organization, $title)
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
        foreach ($this->getAcceptedWords() as $word) {
            if (strpos($word, $this->title) !== false) {
               $this->coutWords = true;
            }
        }
    }


    public function getExcusedWords()
    {
        //
    }
}
