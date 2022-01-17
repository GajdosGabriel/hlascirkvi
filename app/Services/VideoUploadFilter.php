<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\Models\User;
use Alaouy\Youtube\Youtube;
use App\Models\Organization;
use App\Services\ImageResize;
use App\Notifications\Admin\Error;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;


class VideoUploadFilter
{
    public $organization;
    public $title;
   

    public function __construct(Organization $organization, $title)
    {
        $this->organization = $organization;
        $this->title = $title;
     
    }

    public function wordsChecker()
    {
        return $this->countWords();
    }

    public function getExcusedWords()
    {
      //
    }

    public function getAcceptedWords(){
        // Kresťanské spoločenstvo
        if($this->organization->id === 256){
            return [
                'Bohoslužba Banská Bystrica'
            ];
        }
        return [];
    }


    public function countWords()
    {
        $coutWords = 0;

        foreach($this->getAcceptedWords() as $word){
            if (preg_match("/{$word}/", $this->title)) {
                $coutWords++;
            }
        }

        return $coutWords;
    }

}
