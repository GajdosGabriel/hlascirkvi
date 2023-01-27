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
Route::get('test/test', 'TestController@index');
Route::get('test/grecky', 'TestController@greckyMagazin');
Route::get('prayers/fulfilled', 'Api\PrayerController@fulfilled');
Route::get('eventServices/{event}/newReolad', 'Api\EventServiceController@newReolad')->name('eventServices.newReolad');


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResources([
        'notifications'         => Api\NotificationController::class,
        'users'                 => Api\UserController::class,
        'users.organizations'   => Api\UserOrganizationController::class,
        'villages'              => Api\VillageController::class,
        'updaters'              => Api\UpdaterController::class,
    ]);


});

Route::apiResources([
    'prayers'                   => Api\PrayerController::class,
    'posts'                     => Api\PostController::class,
    'postSupport'               => Api\PostSupportController::class,
    'posts.comments'            => Api\PostCommentController::class,
    'organization'              => Api\OrganizationController::class,
    'organizations.favorites'   => Api\OrganizationFavoriteController::class,
    'comments'                  => Api\CommentController::class,
]);



Route::get('artisan/run', function () {

    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('optimize:clear');
    // \Artisan::call('queue:work');

    dd("All is cleared");
});
