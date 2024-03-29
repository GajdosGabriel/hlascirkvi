<?php

namespace App\Services\DetectService;

use DateTime;

class DetectDateTime
{

    public $xxx = 'sssssssssssssssss';

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
            "aprila",
            "mája",
            "júna",
            "júla",
            "augusta",
            "septembra",
            "októbra",
            "novembra",
            "decembra"
        );
        $month_names_official = array(
            "január",
            "február",
            "marec",
            "apriľ",
            "máj",
            "jún",
            "júl",
            "august",
            "september",
            "október",
            "november",
            "december"
        );
        $month_names_incorrect = array(
            "januar",
            "februar",
            "marec",
            "april",
            "maj",
            "jun",
            "jul",
            "august",
            "september",
            "oktober",
            "november",
            "december"
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
        preg_match('/(' 
        . implode('|', $month_names) . '|' 
        . implode('|', $month_names_official) . '|' // ja doprogramoval
        . implode('|', $month_names_incorrect) . '|' //ja doprogramoval
        . implode('|', $short_month_names) . ')[ ,\-_\/]*([0-9]?[0-9])[ ,\-_\/]*(?:' . implode('|', $ordinal_number) . ')?[ ,\-_\/]+([0-9]{4})/i', $string, $matches);
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
            $month = (int) $month + 1;
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
        if (!checkdate($month, (int) $day, (int) $year)) {
            return null;
        } else {

            if ($this->validateDate($date['year'] . '-' . $date['month'] . '-' . $date['day'] . $this->timeRecogniser($string))) {
                return $this->checkYear($date['year']) . '-' . $date['month'] . '-' . $date['day'] . $this->timeRecogniser($string);
            }

            return null;
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

    function checkYear($year)
    {
        if (date("Y") <= $year) {

            return $year;
        }

        return date("Y");
    }


    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
