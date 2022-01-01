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


class ExtractTkkbs extends Extractors
{
    protected $prefix = 'https://www.tkkbs.sk/';
    protected $url = 'https://www.tkkbs.sk/search.php?rstext=pozvanka&rskde=tsl';
    protected $organizationId = 101;


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
        $links = $htmlDom->getElementsByTagName('a');

        //Array that will contain our extracted links.
        $extractedLinks = array();

        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach ($links as $link) {

            //Get the link text.
            $linkText = $link->nodeValue;
            //Get the link in the href attribute.
            $linkHref = $link->getAttribute('href');

            if (stripos($linkHref, 'cisloclanku') == false) {
                continue;
            }

            //Add the link to our $extractedLinks array.
            $extractedLinks[] = array(
                'title' => $linkText,
                'href' =>  $linkHref
            );
        }
        $this->createEvent($extractedLinks);
    }


    public function parseEvent($href, $event)
    {
        // $url = "https://www.ecav.sk/aktuality/pozvanky";
        $html = file_get_contents($this->prefix . $href);
        // $html = file_get_contents('https://www.tkkbs.sk/view.php?cisloclanku=20191219020');

        //Instantiate the DOMDocument class.
        $htmlDom = new DOMDocument;

        //Parse the HTML of the page using DOMDocument::loadHTML
        @$htmlDom->loadHTML($html);

        //Extract the links from the HTML.
        $imgUrls = $htmlDom->getElementsByTagName('img');
        $bodyText = $htmlDom->getElementsByTagName('p');



        //Array that will contain our extracted links.
        $extractedLinks = array();

        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach ($bodyText as $link) {
            //Get the link text.
            $linkText = $link->nodeValue;

            // validation
            if ($linkText == "\n") {
                continue;
            }

            if ($linkText == "") {
                continue;
            }

            $extractedLinks[] = array(
                'src' => "<p>" . $linkText . "</p>"
            );
        }

        // array to string
        $body = implode(" ", array_map(function ($a) {
            return implode("", $a);
        }, $extractedLinks));

        // Remove first sentence
        $moveSentence  = $this->first_sentence_move($body);



        $event->update([
            'body'      => $moveSentence,
            'start_at' => $this->find_date($moveSentence)
        ]);

        // Generate paragraps in the event body
        $this->paragraphGenerator($event);

        // ----------------------------  GET  IMAGE  ----------------------------------
        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach ($imgUrls as $link) {
            //Get the link in the href attribute.
            $linkImg = $link->nodeValue;
            $linkHref = $link->getAttribute('src');

            $extractedSrcLinks[] = array(
                'image' => $linkHref
            );
        }

        // Remove first img src image
        $imgLinks = array_slice($extractedSrcLinks, 1);
        //   dd(  $imgLinks = array_slice($extractedSrcLinks, 1) );


        // Save images from url event
        foreach ($imgLinks as $link) {
            $url =  'https://www.tkkbs.sk/' . $link['image'];

            (new Form($event, $url))->getPictureFromEvent();
        }

        $event->update([
            'village_id' => $this->finderVillages($moveSentence)
        ]);
    }



    //https://www.electrictoolbox.com/get-first-sentence-php/
    public function first_sentence_move($content)
    {

        // Remove "Bratislava 19. decembra (TK KBS)"
        $extract = strpos($content, ')');
        $firstSentence = substr($content, 0, $extract + 2);

        if ($extract === false) {
            return $content;
        }

        $body = str_replace($firstSentence, "", $content);

        return $body . '<p class="text-right">' . $firstSentence . '</p>';
    }
}
