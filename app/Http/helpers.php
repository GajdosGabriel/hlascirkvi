<?php

// Remove HardSpace text
function cleanHardSpace($text)
{
    $text = str_replace("&quot;", '', $text);;
    $text = str_replace("<br>", '', $text);;
    return  $text;
}

// Clean body text
function cleanBody($bodyText)
{
    // New line is required to split non-blank lines
    return preg_replace('~\\s?<p>(\\s|&nbsp;)+</p>\\s?~', '', $bodyText);
}

// Clean body text
function cleanTitle($titleText)
{
    // Remove &nbsp space characters
    $titleText = str_replace("&quot;", ' ', $titleText);
    $titleText = str_replace("&amp;", '', $titleText);
    $titleText = str_replace("&nbsp;", ' ', $titleText);
    // Reome white space
    $titleText = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $titleText);
    return $titleText;
}


/**
 * Localized Date
 *
 * Translate date according to locale settings
 *
 * @param string $format
 * @param string $date
 * @return    string
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

    if (
        strpos($format, 'F') or strpos($format, 'M') or
        strpos($format, 'l') or strpos($format, 'D') or
        strpos($format, 'A') or strpos($format, 'a')
    ) {
        // Load date language lines
        $keys = array(
            'January' => __('date.january'),
            'February' => __('date.february'),
            'March' => __('date.march'),
            'April' => __('date.april'),
            'May' => __('date.may'),
            'June' => __('date.june'),
            'July' => __('date.july'),
            'August' => __('date.august'),
            'September' => __('date.september'),
            'October' => __('date.october'),
            'November' => __('date.november'),
            'December' => __('date.december'),

            'Jan' => __('date.jan'),
            'Feb' => __('date.feb'),
            'Mar' => __('date.mar'),
            'Apr' => __('date.apr'),
            'May' => __('date.may'),
            'Jun' => __('date.jun'),
            'Jul' => __('date.jul'),
            'Aug' => __('date.aug'),
            'Sep' => __('date.sep'),
            'Oct' => __('date.oct'),
            'Nov' => __('date.nov'),
            'Dec' => __('date.dec'),

            'Monday' => __('date.monday'),
            'Tuesday' => __('date.tuesday'),
            'Wednesday' => __('date.wednesday'),
            'Thursday' => __('date.thursday'),
            'Friday' => __('date.friday'),
            'Saturday' => __('date.saturday'),
            'Sunday' => __('date.sunday'),

            'Mon' => __('date.mon'),
            'Tue' => __('date.tue'),
            'Wed' => __('date.wed'),
            'Thu' => __('date.thu'),
            'Fri' => __('date.fri'),
            'Sat' => __('date.sat'),
            'Sun' => __('date.sun'),

            'AM' => __('date.am_uppercase'),
            'PM' => __('date.pm_uppercase'),
            'am' => __('date.am_lowercase'),
            'pm' => __('date.pm_lowercase')
        );

        // Translate date
        $result = strtr($result, $keys);
    }

    return trim($result);
}

# url_get_contents function by Andy Langton: https://andylangton.co.uk/
function url_get_contents(
    $url,
    $useragent = 'cURL',
    $headers = false,
    $follow_redirects = false,
    $debug = false
) {

    # initialise the CURL library
    $ch = curl_init();

    # specify the URL to be retrieved
    curl_setopt($ch, CURLOPT_URL, $url);

    # we want to get the contents of the URL and store it in a variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    # specify the useragent: this is a required courtesy to site owners
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

    # ignore SSL errors
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    # return headers as requested
    if ($headers == true) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
    }

    # only return headers
    if ($headers == 'headers only') {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }

    # follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
    if ($follow_redirects == true) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    }

    # if debugging, return an array with CURL's debug info and the URL contents
    if ($debug == true) {
        $result['contents'] = curl_exec($ch);
        $result['info'] = curl_getinfo($ch);
    } # otherwise just return the contents as a variable
    else $result = curl_exec($ch);

    # free resources
    curl_close($ch);

    # send back the data
    return $result;
}
