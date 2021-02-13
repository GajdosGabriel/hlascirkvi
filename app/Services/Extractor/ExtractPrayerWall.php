<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 12. 12. 2019
 * Time: 10:02
 */

namespace App\Services\Extractor;


use App\Services\Extractor\Extractors;
use App\Services\Form;
use DOMDocument;
use DOMXPath;
use Imagick;


class ExtractPrayerWall extends Extractors
{
    protected $prefix = 'https://www.mojakomunita.sk/';
//    protected $url = 'https://www.mojakomunita.sk/web/modlitba';
    protected $url = 'https://www.zzm.sk/prosby-2/';
    protected $organizationId = 1;


    public function parseListUrl() {
//        $url = "https://www.tkkbs.sk/search.php?rstext=pozvanka&rskde=tsl";
        $html = file_get_contents($this->url);
//        $html = file_get_contents($this->url);

        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);

        $finder = new DomXPath($htmlDom);
        $classBody="gb-entry-content";
//        $classname="entry-data-e";
//        $classname="pBody";
//        $classname="pHead";
//        $classname="user-name";
        $prayersBody = $finder->query("//*[contains(@class, '$classBody')]");


       $className="gb-author-name";
       $prayersTitle = $finder->query("//*[contains(@class, '$className')]");


        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach($prayersTitle as $link){

            //Get the link text.
            $linkText = $link->nodeValue;


            //Add the link to our $extractedLinks array.
            $extractedTitle[] = array(
                'user' => trim(preg_replace('/\t/', '', $linkText))
            );
        }


        foreach($prayersBody as $link){

            //Get the link text.
            $linkText = $link->nodeValue;


            //Add the link to our $extractedLinks array.
            $extractedBody[] = array(
                'body' => trim(preg_replace('/\t/', '', $linkText))
            );
        }


    //    return $extractedTitle;
    //    return $extractedBody;
    $merged = array_replace_recursive($extractedTitle,$extractedBody);

       $this->createPrayer($merged);
    }




    //https://www.electrictoolbox.com/get-first-sentence-php/
    public function first_sentence_move($content) {

        // Remove "Bratislava 19. decembra (TK KBS)"
        $extract = strpos($content, ')');
        $firstSentence = substr($content, 0, $extract+2);

        if($extract === false) {
            return $content;
        }

        $body = str_replace($firstSentence, "", $content);

        return $body . '<p class="text-right">'. $firstSentence . '</p>';
    }












}
