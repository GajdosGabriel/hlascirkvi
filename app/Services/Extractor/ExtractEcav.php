<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 12. 12. 2019
 * Time: 10:02
 */

namespace App\Services\Extractor;


use App\Services\Form;
use DOMDocument;
use DOMXPath;




class ExtractEcav extends Extractors
{
    protected $prefix = 'https://www.ecav.sk';
    protected $url = 'https://www.ecav.sk/aktuality/pozvanky';
    protected $organizationId = 102;


    public function parseListUrl() {
        $html = file_get_contents($this->url);

        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);

        //Extract the links from the HTML.
        $links = $htmlDom->getElementsByTagName('a');

        //Array that will contain our extracted links.
        $extractedLinks = array();

        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach($links as $link){

            //Get the link text.
            $linkText = $link->nodeValue;
            //Get the link in the href attribute.
            $linkHref = $link->getAttribute('href');

            //If the link is empty, skip it and don't
            //add it to our $extractedLinks array
            if(strlen(trim($linkHref)) == 0){
                continue;
            }

            //Skip if it is a hashtag / anchor link.
            if($linkHref[0] == '#'){
                continue;
            }

            if(! stripos($linkHref ,'aktuality/pozvanky/' ) ){
                continue;
            }

            // validation
            if($linkText == "\n" or $linkHref == "\n" ){
                continue;
            }

            //Add the link to our $extractedLinks array.
            $extractedLinks[] = array(
                'title' => mb_convert_encoding($linkText, "Windows-1252", "UTF-8"),
                'href' =>  mb_convert_encoding($linkHref, "Windows-1252", "UTF-8")
            );

        }

        $this->createEvent($extractedLinks);
    }

    public function parseEvent($href, $event) {
//        $url = "https://www.ecav.sk/aktuality/pozvanky";
        $html = file_get_contents($this->prefix . $href);
//        $html = file_get_contents('https://www.ecav.sk/aktuality/pozvanky/spevacky-zbor-z-diakoviec-pozyva-na-koncert');


        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);

        //Extract the links from the HTML.
        $bodyText = $htmlDom->getElementsByTagName('p');

        //Array that will contain our extracted links.
        $extractedLinks = array();

        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach( $bodyText as $link){
            //Get the link text.
            $linkText = $link->nodeValue;

            // validation
            if($linkText == "\n"){
                continue;
            }

            if($linkText == ""){
                continue;
            }

            $linkText = preg_replace('/\xc2\xa0/', ' ', $linkText);
            $linkText =  mb_convert_encoding($linkText, "Windows-1252", "UTF-8");
//            Convert to string from Binary casting
//            $linkText = preg_replace('/[[:^print:]]/', '', $linkText);


//            $extractedLinks[] = array(
//                'src' => $linkText
//            );
//            dd($extractedLinks);


            // Because is multi arrays
            $event->update([
                'body' => $event->body . '<p>' . $linkText. '</p>'
            ]);
        }

//        $linkText =  mb_convert_encoding($event->body, "Windows-1252", "UTF-8");

//        dd($linkText);
//        $event->update([
//            'body' => $linkText
//        ]);






// ----------------------------  GET  IMAGE  ----------------------------------

        //Extract the img from the HTML.
        $imgUrls = $htmlDom->getElementsByTagName('img');
        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach( $imgUrls as $link){
            //Get the link in the href attribute.
            $linkHref = $link->getAttribute('src');

            if(! stripos($linkHref ,'/rails/active_storage/' ) ){
                continue;
            }

            //Add the link to our $extractedLinks array.
//            $extractedLinks[] = array(
//                'src' => $linkHref
//            );
//
//            dd($extractedLinks);

            (new Form($event, $linkHref))->getPictureEcavEvent();

            break;
        }





        // Try find village from body text
        $event->update([
            'village_id' => $this->finderVillages($event->body)
        ]);

////         Dočasne vypnuté
//               $event->update([
//            'start_at' => $this->find_date($event->body),
//        ]);


    }




}
