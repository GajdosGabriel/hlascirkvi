<?php

namespace App\Http\Controllers\Api;

use Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RssController extends Controller
{
    public function getRssCanal($canal) {

        // xml file path
//        $path = "https://www.tkkbs.sk/rss/vsetky/";
        $path = "https://www.tkkbs.sk/rss/" . $canal .'/';

// Read entire file into string
        $xmlfile = file_get_contents($path);

// Convert xml string into an object
        $new = simplexml_load_string($xmlfile);

// Convert into json
        $con = json_encode($new);

// Convert into associative array
        $rss = json_decode($con, true);

        return $rss;

    }

}
