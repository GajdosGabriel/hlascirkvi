<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 12. 12. 2019
 * Time: 10:02
 */

namespace App\Services\Extractor;

use DOMXPath;
use DOMDocument;
use Carbon\Carbon;
use App\Services\Form;
use App\Services\DetectService\DetectDateTime;




class ExtractEcav extends Extractors
{
    protected $prefix = 'https://www.ecav.sk';
    protected $url = 'https://www.ecav.sk/aktuality/pozvanky';
    protected $organizationId = 102;
    public $detectDateTime;


    public function __construct()
    {
        $this->detectDateTime = new DetectDateTime();
        $this->setOrganization($this->organizationId);
    }


    public function parseListUrl()
    {

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
        foreach ($links as $link) {

            //Get the link text.
            $linkText = $link->nodeValue;
            //Get the link in the href attribute.
            $linkHref = $link->getAttribute('href');

            //If the link is empty, skip it and don't
            //add it to our $extractedLinks array
            if (strlen(trim($linkHref)) == 0) {
                continue;
            }

            //Skip if it is a hashtag / anchor link.
            if ($linkHref[0] == '#') {
                continue;
            }

            if (!stripos($linkHref, 'aktuality/pozvanky/')) {
                continue;
            }

            // validation
            if ($linkText == "\n" or $linkHref == "\n") {
                continue;
            }

            //Add the link to our $extractedLinks array.
            $extractedLinks[] = array(
                'title' => utf8_decode($linkText),
                'href' => $this->prefix . $linkHref
            );
        }
        $this->createEvent($extractedLinks);
    }

    public function parseEvent($href, $event = null)
    {
        // Používam pri znova načítani cez EventServiceController
        if($event)
        $this->event = $event;

        $html = file_get_contents($href);
        // $html = file_get_contents('https://www.ecav.sk/aktuality/pozvanky/spevacky-zbor-z-diakoviec-pozyva-na-koncert');


        $dom = new DOMDocument();

        @$dom->loadHTML($html);

        // $divs = $dom->getElementsByTagName('div');


        $xpath = new DOMXPath($dom);

        $content = $xpath->query("//div[@class='fr-view']");
        //Array that will contain our extracted links.
        $div_array = null;

        foreach ($content as $div) {
            $div_text = $div->textContent;

            $div_text = preg_replace('/\xc2\xa0/', ' ', $div_text);
            // $div_text=  mb_convert_encoding($div_text, "Windows-1252", "UTF-8");
            $div_array =  $div_text;
        }

        $this->event->update([
            'body' =>  utf8_decode($div_array)
        ]);


        // ----------------------------  GET  IMAGE  ----------------------------------

        //Extract the img from the HTML.
        $imgUrls =  $dom->getElementsByTagName('img');


        //Loop through the DOMNodeList.
        //We can do this because the DOMNodeList object is traversable.
        foreach ($imgUrls as $link) {
            //Get the link in the href attribute.
            $linkHref = $link->getAttribute('src');


            if (!stripos($linkHref, '/rails/active_storage/')) {
                continue;
            }

            (new Form($this->event, $linkHref))->getPictureFromEvent();

            break;
        }



        // Try find village from body text
        $this->event->update([
            'village_id' => $this->finderVillages($this->event->body)
        ]);



        // Detect datetime
        $startAt = $this->detectDateTime->find_date($this->event->body);

        $this->event->update([
            'start_at' => $startAt,
            'end_at' => Carbon::parse($startAt)->addHours(2)
        ]);
    }
}
