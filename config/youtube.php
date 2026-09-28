<?php

/*
|--------------------------------------------------------------------------
| Laravel PHP Facade/Wrapper for the Youtube Data API v3
|--------------------------------------------------------------------------
|
| Here is where you can set your key for Youtube API. In case you do not
| have it, it can be acquired from: https://console.developers.google.com
*/

return [
    'key' => env('YOUTUBE_API_KEY', 'YOUR_API_KEY'),
    'name_search' => [
        'max_pages_per_run' => 40,
        'max_pages_per_canal' => 3,
        'initial_days' => 30,
        'overlap_days' => 2,
    ],
];
