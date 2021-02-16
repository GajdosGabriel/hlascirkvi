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


class ExtractMojaKomunita extends Extractors
{
    protected $prefix = 'https://www.mojakomunita.sk/';
//    protected $url = 'https://www.mojakomunita.sk/web/modlitba';
    protected $url = 'https://www.mojakomunita.sk/web/modlitba';
    // protected $url = 'https://www.zzm.sk/prosby-2/?pageNum=3';
    protected $organizationId = 1;


    public function parseListUrl()
    {
//        $url = "https://www.tkkbs.sk/search.php?rstext=pozvanka&rskde=tsl";
        $html = file_get_contents($this->url);
//        $html = file_get_contents($this->url);

        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);


        //Extract the links from the HTML.
        $links = $htmlDom->getElementsByTagName('h3');


        foreach ($links as $link) {

            //Get the link text.
            $linkText = $link->nodeValue;


            //Add the link to our $extractedLinks array.
            $extractedTitle[] = array(
                'title' => mb_convert_encoding($linkText, "Windows-1252", "UTF-8"),
            );

        }


        $finder = new DomXPath($htmlDom);

        $className = "user-name";
        $userName = $finder->query("//*[contains(@class, '$className')]");

        foreach ($userName as $link) {

            //Get the link text.
            $linkText = $link->nodeValue;

            $encoding = trim(preg_replace('/\t/', '', $linkText));

            //Add the link to our $extractedLinks array.
            $extractedUser[] = array(
                'user' => mb_convert_encoding($encoding, "Windows-1252", "UTF-8")
            );
        }


        $classBody = "pBody";
        $prayersBody = $finder->query("//*[contains(@class, '$classBody')]");

        foreach ($prayersBody as $link) {

            //Get the link text.
            $linkText = $link->nodeValue;

            $encoding = trim(preg_replace('/\t/', '', $linkText));

            //Add the link to our $extractedLinks array.
            $extractedBody[] = array(
                'body' => mb_convert_encoding($encoding, "Windows-1252", "UTF-8")
            );
        }

        //   Merge all arrays;
        $merged = array_replace_recursive($extractedUser, $extractedBody, $extractedTitle);

        // Remove user from Title
        foreach ($merged as $item){
            $toSave[] = array(
                'user' => $item['user'],
                'title' => str_replace( $item['user'], '', $item['title']),
                'body' => $item['body']
            );
        }

       $this->createPrayer($toSave);
    }


}
