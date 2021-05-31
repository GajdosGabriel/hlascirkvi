<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::get('rss-reader-canal/{canal}', 'Api\RssController@getRssCanal');
Route::get('test/newsletter', 'TestController@newsletter');
Route::get('test/grecky', 'TestController@greckyMagazin');
Route::get('prayers/fulfilled', 'Api\PrayerController@fulfilled');

Route::apiResources([
    'modlitby' => Api\PrayerController::class,
    'posts' => Api\PostController::class,
]);



Route::get('artisan/run', function () {

    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('optimize:clear');

    dd("All is cleared");

});
