<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});


Route::get('rss-reader-canal/{canal}', 'Api\RssController@getRssCanal');
Route::get('test/newsletter', 'TestController@newsletter');
Route::get('test/grecky', 'TestController@greckyMagazin');
Route::get('prayers/fulfilled', 'Api\PrayerController@fulfilled');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResources([
        'notifications'     => Api\NotificationController::class,
    ]);
});

Route::apiResources([
    'modlitby'          => Api\PrayerController::class,
    'posts'             => Api\PostController::class,
    'posts.comments'    => Api\PostsCommentsController::class,
    'organizations'     => Api\OrganizationController::class,
    'comments'          => Api\CommentController::class,
]);



Route::get('artisan/run', function () {

    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('optimize:clear');

    dd("All is cleared");
});
