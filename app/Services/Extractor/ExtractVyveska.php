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

class ExtractVyveska extends Extractors
{
    protected $prefix = 'http://www.vyveska.sk/';
    protected $url = 'http://www.vyveska.sk/22601/milujuca-naruc-duchovne-cvicenia-pre-zranenych-spontannym-potratom-a-bezdetne-pary.html';
    protected $organizationId = 1;


    public function parseListUrl()
    {
        // xml file path
//        $path = "https://www.tkkbs.sk/rss/vsetky/";
        $path = "http://www.vyveska.sk/rss.xml";

        // Read entire file into string
        $xmlfile = file_get_contents($path);

        // Convert xml string into an object
        $new = simplexml_load_string($xmlfile);

        // Convert into json
        $con = json_encode($new);

        // Convert into associative array
        $links = json_decode($con, true);

        // return $rss['channel']['item'];

        $extractedLinks = array();

        foreach ($links['channel']['item'] as $link) {

            //Add the link to our $extractedLinks array.
            $extractedLinks[] = array(
                        'title' => $link['title'],
                        'href' => str_replace('?utm_source=vyveska.sk&utm_content=simple&utm_medium=rss', '', str_replace('http://www.vyveska.sk/', '', $link['link']))

                    );
        }

        // return $extractedLinks;
        $this->createEvent($extractedLinks);
    }



    public function parseEvent($href, $event)
    {



        // $url = "https://www.ecav.sk/aktuality/pozvanky";
        $html = file_get_contents($href);
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

            if ($linkText == 'Prihláste sa do newslettra vyveska.sk') {
                continue;
            }

            $extractedLinks[] = array(
                'src' =>    '<p>' . $linkText . '</p>'
            );
        }

        // Odstránenie prvého array newslettra
        $textBody = array_slice($extractedLinks, 1);
        // Vybratie body z jedotlivých arrays
        $textBody =  collect($textBody)->implode('src', ' ');



        // dd( $textBody );

        $event->update([
            'body'      =>  $textBody,
            'village_id' => $this->finderVillages(implode("|", $extractedLinks[0])),
            // 'start_at' => $this->find_date($moveSentence)
        ]);

        // ----------------------------  GET  IMAGE  ----------------------------------
        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach ($imgUrls as $link) {
            //Get the link in the href attribute.
            $linkImg = $link->nodeValue;
            $linkHref = $link->getAttribute('src');


            if (stripos(json_encode($linkHref),'banners') !== false) {
                continue;
            }

            if (stripos(json_encode($linkHref),'designe') !== false) {
                continue;
            }

            $extractedSrcLinks[] = array(
                'image' => $linkHref
            );
        }

        // dd($extractedSrcLinks[0]);

        // get správny img z poradí
        $imgLinks = implode("|", $extractedSrcLinks[0]);

        // dd($imgLinks);

        // Save images from url event
        (new Form($event, $this->prefix . $imgLinks))->getPictureEcavEvent();
    }
}
