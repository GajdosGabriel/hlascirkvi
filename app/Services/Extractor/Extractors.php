<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21. 12. 2019
 * Time: 10:35
 */

namespace App\Services\Extractor;


use DB;
use Carbon\Carbon;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Services\DetectService\DetectDateTime;

abstract class Extractors
{
    protected $event;
    protected $detectDateTime;

    public function __construct()
    {
        $this->event = new EloquentEventRepository;
        $this->detectDateTime = new detectDateTime;
    }

    protected function createPrayer($data)
    {

        foreach ($data as $item) {

            // Find or create new record
            if (DB::table('prayers')->whereBody($item['body'])->first()) {
                continue;
            }

            DB::table('prayers')->insert([
                'title' => isset($item['title'])  ? $item['title'] : '',
                'body' => $item['body'],
                'user_name' => $item['user'],
                'organization_id' => $item['organization'],
                'created_at' => Carbon::now()->subHours(2)->addMinute(rand(3, 55))->toDateTimeString(),
            ]);
        }
    }

    protected function createEvent($data)
    {
        foreach ($data as $item) {
            // Remove white space from left
            $title = trim($item['title']);
            // Odstránenie úvodzoviek vpredu a vzadu
            $title = str_replace(['„', '“'], '', $item['title']);

            // Remove extra spaces but not space between two words
            $title = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $title)));


            // Remove extra spaces but not space between two words "a&nbsp;"
            $title = preg_replace('/\xc2\xa0/', ' ', $title);

            // Find existing or create new record
            if (DB::table('events')->whereTitle($title)
                // ->whereOrganization_id($this->organizationId)
                ->where('created_at', '>', Carbon::now()->subMonths(2))
                ->first()
            ) {
                continue;
            }

            $event = $this->event->create([
                'title' => $title,
                'body' => '',
                'organization_id' => $this->organizationId,
                'start_at' => Carbon::now()->toDateTimeString(),
                'end_at' => Carbon::now()->addHours(2)->toDateTimeString(),
                'registration' => 'no',
                'village_id' => 4209, // Celé Slovensko
                'entryFee' => 'no',
                'published' => date("Y-m-d H:i:s"),
                'created_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                'orginal_source' => $item['href'],
            ]);
            $this->parseEvent($item['href'], $event);
        }
    }


    public function finderVillages($content)
    {
        // Najdlhšie name na začiatok a DESC po shortes
        $villages = DB::table('villages')->orderByRaw('(CHAR_LENGTH(fullname)) DESC')->get();
        //        $villages = DB::table('villages')->get();

        foreach ($villages as $village) {
            // Find whole word
            if (strpos($content, $village->fullname)) {
                //            if (  preg_match("/\b$village->fullname\b/" , $content)  ) {

                return $village->id;
                break;
            }
        }
        // Region not found
        return 4209; // Celé Slovensko
    }





    public function paragraphGenerator($event, $length = 350, $maxLength = 499)
    {

        $text = $event->body;

        //Text length
        $textLength = strlen($text);

        //initialize empty array to store split text
        $splitText = array();

        //return without breaking if text is already short
        if (!($textLength > $maxLength)) {
            $splitText[] = $text;
            return $splitText;
        }

        //Guess sentence completion
        $needle = ". ";

        /*iterate over $text length
          as substr_replace deleting it*/
        while (strlen($text) > $length) {

            $end = strpos($text, $needle, $length);

            if ($end === false) {

                //Returns FALSE if the needle (in this case ".") was not found.
                $splitText[] = substr($text, 0);
                $text = '';
                break;
            }

            $end++;
            $splitText[] = substr($text, 0, $end);
            $text = substr_replace($text, '', 0, $end);
        }

        if ($text) {
            $splitText[] = substr($text, 0);
        }

        //  clean before updating
        $event->update([
            'body' => ''
        ]);

        foreach ($splitText as $text) {
            $event->update([
                'body' => $event->body . '<p>' . $text . '</p>'
            ]);
        }


        return $splitText;
    }
}
