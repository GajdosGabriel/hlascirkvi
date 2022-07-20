<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 12. 12. 2019
 * Time: 10:02
 */

namespace App\Services\Extractor;

use App\Models\FirstName;
use App\Services\Extractor\Extractors;
use App\Services\Form;
use DOMDocument;
use DOMXPath;
use Imagick;


class ExtractSluzobniceDuchaSvateho extends Extractors
{
    protected $prefix = 'https://www.sspsap.sk/prosby-o-modlitbu/';
    protected $url = 'https://www.sspsap.sk/prosby-o-modlitbu/';




    public function parseListUrl() {
//        $url = "https://www.tkkbs.sk/search.php?rstext=pozvanka&rskde=tsl";
        $html = file_get_contents($this->url);
//        $html = file_get_contents($this->url);

        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);

        $finder = new DomXPath($htmlDom);


        $classBody="prayer-text";

        $prayersBody = $finder->query("//*[contains(@class, '$classBody')]");


        foreach($prayersBody as $link){

            //Get the link text.
            $linkText = $link->nodeValue;


            //Add the link to our $extractedLinks array.
            $extractedBody[] = array(
                'body' => trim(preg_replace('/\t/', '', $linkText)),
                'user' => $this->getRandomName(),
                'title' => 'Prosba o modlitbu',
                'organization' => 650
            );
        }


    // dd($extractedBody);

       $this->createPrayer($extractedBody);
    }


    public function getRandomName(){
       return FirstName::orderBy('count', 'asc')->whereId(rand(1, 1000))->first()->name;
    }



}
