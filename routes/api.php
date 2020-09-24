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
Route::get('test/test', 'TestController@test');
Route::get('test/grecky', 'TestController@greckyMagazin');
