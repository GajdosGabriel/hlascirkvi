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


abstract class Extractors
{
    public function __construct()
    {
        $this->event = new EloquentEventRepository;
    }

    protected function createPrayer($data)
    {

        foreach ($data as $item) {

            // Find or create new record
            if (DB::table('prayers')->whereBody($item['body'])->first()) {
                continue;
            }


            // Find spam if contains I.
            if (str_contains($item['body'], 'More Info:')) {
                continue;
            }

            // Find spam if contains II.
            if (str_contains($item['body'], 'Start Game:')) {
                continue;
            }

            DB::table('prayers')->insert([
                'title' => isset($item['title'])  ? $item['title'] : '',
                'body' => $item['body'],
                'user_name' => $item['user'],
                'user_id' => 100,
                'created_at' => Carbon::now()->subHours(2)->addMinute(rand(3, 55))->toDateTimeString(),
            ]);
        }
    }

    protected function createEvent($data, $published = 0)
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
                ->where('created_at', '>', Carbon::now()->subMonths(3))
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
                'published' => $published,
                'created_at' => Carbon::now()->subHours(2)->toDateTimeString(),
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

    //https://github.com/etiennetremel/PHP-Find-Date-in-String
    /**
     * Find Date in a String
     *
     * @param string  find_date( ' some text 01/01/2012 some text' ) or find_date( ' some text October 5th 86 some text' )
     * @return mixed  false if no date found else array: array( 'day' => 01, 'month' => 01, 'year' => 2012 )
     * @link     http://www.etiennetremel.net
     * @version  0.2.0
     *
     * @author   Etienne Tremel
     * @license  http://creativecommons.org/licenses/by/3.0/ CC by 3.0
     */
    //    protected $string = 'some text 01/01/2012 some text v vvvv ';
    public function find_date($string)
    {
        $shortenize = function ($string) {
            return substr($string, 0, 3);
        };
        // Define month name:
        $month_names = array(
            "januára",
            "februára",
            "marca",
            "apriľa",
            "mája",
            "júna",
            "júla",
            "augusta",
            "septembra",
            "októbra",
            "novembra",
            "decembra"
        );
        $short_month_names = array_map($shortenize, $month_names);
        // Define day name
        $day_names = array(
            "pondelok",
            "utorok",
            "streda",
            "štvrtok",
            "piatok",
            "sobota",
            "nedeľa"
        );
        $short_day_names = array_map($shortenize, $day_names);
        // Define ordinal number
        $ordinal_number = ['.', 'nd', 'rd', 'th'];
        $day = "";
        $month = "";
        $year = "";
        // Match dates: 01/01/2012 or 30-12-11 or 1 2 1985
        preg_match('/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/', $string, $matches);
        if ($matches) {
            if ($matches[1])
                $day = $matches[1];
            if ($matches[2])
                $month = $matches[2];
            if ($matches[3])
                $year = $matches[3];
        }
        // Match dates: Sunday 1st March 2015; Sunday, 1 March 2015; Sun 1 Mar 2015; Sun-1-March-2015
        preg_match('/(?:(?:' . implode('|', $day_names) . '|' . implode('|', $short_day_names) . ')[ ,\-_\/]*)?([0-9]?[0-9])[ ,\-_\/]*(?:' . implode('|', $ordinal_number) . ')?[ ,\-_\/]*(' . implode('|', $month_names) . '|' . implode('|', $short_month_names) . ')[ ,\-_\/]+([0-9]{4})/i', $string, $matches);
        if ($matches) {
            if (empty($day) && $matches[1])
                $day = $matches[1];
            if (empty($month) && $matches[2]) {
                $month = array_search(strtolower($matches[2]), $short_month_names);
                if (!$month)
                    $month = array_search(strtolower($matches[2]), $month_names);
                $month = $month + 1;
            }
            if (empty($year) && $matches[3])
                $year = $matches[3];
        }
        // Match dates: March 1st 2015; March 1 2015; March-1st-2015
        preg_match('/(' . implode('|', $month_names) . '|' . implode('|', $short_month_names) . ')[ ,\-_\/]*([0-9]?[0-9])[ ,\-_\/]*(?:' . implode('|', $ordinal_number) . ')?[ ,\-_\/]+([0-9]{4})/i', $string, $matches);
        if ($matches) {
            if (empty($month) && $matches[1]) {
                $month = array_search(strtolower($matches[1]), $short_month_names);
                if (!$month)
                    $month = array_search(strtolower($matches[1]), $month_names);
                $month = $month + 1;
            }
            if (empty($day) && $matches[2])
                $day = $matches[2];
            if (empty($year) && $matches[3])
                $year = $matches[3];
        }
        // Match month name:
        if (empty($month)) {
            preg_match('/(' . implode('|', $month_names) . ')/i', $string, $matches_month_word);
            if ($matches_month_word && $matches_month_word[1])
                $month = array_search(strtolower($matches_month_word[1]), $month_names);
            // Match short month names
            if (empty($month)) {
                preg_match('/(' . implode('|', $short_month_names) . ')/i', $string, $matches_month_word);
                if ($matches_month_word && $matches_month_word[1])
                    $month = array_search(strtolower($matches_month_word[1]), $short_month_names);
            }
            $month = $month + 1;
        }
        // Match 5th 1st day:
        if (empty($day)) {
            preg_match('/([0-9]?[0-9])(' . implode('|', $ordinal_number) . ')/', $string, $matches_day);
            if ($matches_day && $matches_day[1])
                $day = $matches_day[1];
        }
        // Match Year if not already setted:
        if (empty($year)) {
            preg_match('/[0-9]{4}/', $string, $matches_year);
            if ($matches_year && $matches_year[0])
                $year = $matches_year[0];
        }
        if (!empty($day) && !empty($month) && empty($year)) {
            preg_match('/[0-9]{2}/', $string, $matches_year);
            if ($matches_year && $matches_year[0])
                $year = $matches_year[0];
        }
        // Day leading 0
        if (1 == strlen($day))
            $day = '0' . $day;
        // Month leading 0
        if (1 == strlen($month))
            $month = '0' . $month;
        // Check year:
        if (2 == strlen($year) && $year > 20)
            $year = '19' . $year;
        else if (2 == strlen($year) && $year < 20)
            $year = '20' . $year;
        $date = array(
            'year' => $year,
            'month' => $month,
            'day' => $day
        );
        // Return false if nothing found:
        if (!checkdate($month, $day, $year)) {
            return false;
        } else {

            return $date['year'] . '-' . $date['month'] . '-' . $date['day'] . $this->timeRecogniser($string);
            //  return $date['year'].'-'.$date['month'].'-'.$date['day'] . ' 23:59:59';
        }
    }

    public function timeRecogniser($text)
    {

        $time = preg_match_all("/[0-9][0-9]:[0-9][0-9]/", $text, $array);


        if (!empty($time)) {

            $hours = substr($array[0][0], 0, 2);
            $minutes = substr($array[0][0], 3, 5);
            return ' ' . $hours . ':' . $minutes . ':00';
        }

        return ' 00:00:00';
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
