<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Verse;
use Illuminate\Http\Request;

class VerseController extends Controller
{
    public function index($slug=null) {

        if($slug==null){
            $verse = Verse::find(now()->dayOfYear);
            $id = $verse->id;
        }else{
            $verse = Verse::whereSlug($slug)->first();

            $id = $verse->id;
        }

        // Previous link - get slug
        if ( ($id) < 1 ) {
            $previous = Verse::find( Ver::count() )->slug;
        } else {
            $previous = Verse::find($id - 1)->slug;
        }

        // Next link
        if ( ($id) < Verse::count() ) {
            $next = Verse::find($id + 1)->slug;
        } else {
            $next = Verse::first()->slug;
        }

        $date = $this->date_from_day_of_year(null, $id);

        return view('verses.show')
            ->with('previous', $previous)
            ->with('next', $next)
            ->with('post', $verse)
            ->with('date', $date)
            ->with('title', trans('web.daily_read'));
    }


    /**
     * Create Localized Date from Day of Year
     *
     * Create custom date according to locale settings
     *
     * @param   string   $format
     * @param   integer  $dayOfYear
     * @return  string
     */
    function date_from_day_of_year($format = null, $dayOfYear = null, $year = null)
    {
        $format = ($format === null) ? 'j. F Y' : $format;

        $dayOfYear = ($dayOfYear === null) ? now()->dayOfYear : $dayOfYear - 1;

        $year = ($year === null) ? now()->year : $year;

        $dt = \Carbon\Carbon::create($year, 1, 1, 0)->addSeconds($dayOfYear*86400)->toDateTimeString();

        return $this->localized_date($format, $dt);
    }

    /**
     * Localized Date
     *
     * Translate date according to locale settings
     *
     * @param  	string 	$format
     * @param  	string 	$date
     * @return 	string
     */
    function localized_date($format, $date)
    {
        // Clasic date function - return English version of date
        $result = date($format, strtotime($date));

        // Format 'F' - A full textual representation of a month
        // Format 'M' - A short textual representation of a month, three letters
        // Format 'l' - A full textual representation of the day of the week
        // Format 'D' - A textual representation of a day, three letters
        // Format 'A' - Uppercase AM or PM
        // Format 'a' - Lowercase am or pm

        // Add space before format string
        $format = ' ' . $format;

        if (strpos($format, 'F') or strpos($format, 'M') or
            strpos($format, 'l') or strpos($format, 'D') or
            strpos($format, 'A') or strpos($format, 'a')
        ) {
            // Load date language lines
            $keys = array(
                'January'   => __('date.january'),
                'February'  => __('date.february'),
                'March'     => __('date.march'),
                'April'     => __('date.april'),
                'May'       => __('date.may'),
                'June'      => __('date.june'),
                'July'      => __('date.july'),
                'August'    => __('date.august'),
                'September' => __('date.september'),
                'October'   => __('date.october'),
                'November'  => __('date.november'),
                'December'  => __('date.december'),

                'Jan'       => __('date.jan'),
                'Feb'       => __('date.feb'),
                'Mar'       => __('date.mar'),
                'Apr'       => __('date.apr'),
                'May'       => __('date.may'),
                'Jun'       => __('date.jun'),
                'Jul'       => __('date.jul'),
                'Aug'       => __('date.aug'),
                'Sep'       => __('date.sep'),
                'Oct'       => __('date.oct'),
                'Nov'       => __('date.nov'),
                'Dec'       => __('date.dec'),

                'Monday'    => __('date.monday'),
                'Tuesday'   => __('date.tuesday'),
                'Wednesday' => __('date.wednesday'),
                'Thursday'  => __('date.thursday'),
                'Friday'    => __('date.friday'),
                'Saturday'  => __('date.saturday'),
                'Sunday'    => __('date.sunday'),

                'Mon'       => __('date.mon'),
                'Tue'       => __('date.tue'),
                'Wed'       => __('date.wed'),
                'Thu'       => __('date.thu'),
                'Fri'       => __('date.fri'),
                'Sat'       => __('date.sat'),
                'Sun'       => __('date.sun'),

                'AM'        => __('date.am_uppercase'),
                'PM'        => __('date.pm_uppercase'),
                'am'        => __('date.am_lowercase'),
                'pm'        => __('date.pm_lowercase')
            );

            // Translate date
            $result = strtr($result, $keys);
        }

        return trim($result);
    }

}
