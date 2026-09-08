<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Verejné sú len čítacie endpointy a tie tri zápisy, ktoré sú zámernou
| funkciou webu: anonymný komentár, anonymná modlitba a označenie kanála
| ako obľúbeného bez prihlásenia (viď App\Traits\HasComments::addComment).
| Všetko ostatné patrí za auth:sanctum — EnsureFrontendRequestsAreStateful
| v skupine `api` (app/Http/Kernel.php:44) pustí prihláseného SPA klienta
| cez session cookie, takže tokeny netreba.
|
*/

/*
 * Verejné čítanie
 */
Route::apiResource('prayers', Api\PrayerController::class)->only(['index']);
Route::get('prayers/fulfilled', 'Api\PrayerController@fulfilled')->name('prayers.fulfilled');

Route::apiResource('posts', Api\PostController::class)->only(['index']);
Route::apiResource('comments', Api\CommentController::class)->only(['index']);
Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['index']);
Route::apiResource('organization', Api\OrganizationController::class)->only(['show']);

Route::get('rss-reader-canal/{canal}', 'Api\RssController@getRssCanal')
    ->whereIn('canal', ['domov', 'zahranicie', 'press'])
    ->name('api.rss');

/*
 * Verejné zápisy — anonymný komentár a anonymné označenie obľúbeného kanála
 * sú funkcia webu, nie diera. Sú preto obmedzené sadzbou.
 */
Route::middleware('throttle:10,1')->group(function () {
    Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['store']);
    Route::apiResource('organizations.favorites', Api\OrganizationFavoriteController::class)->only(['store']);

    // Modlitbu vie pridať aj neprihlásený — formulár od neho žiada e-mail
    // (resources/js/prayer/ModalNewPrayer.vue:103) a EloquentUserRepository
    // ::checkIfUserAccountExist mu podľa neho založí a prihlási účet.
    Route::apiResource('prayers', Api\PrayerController::class)->only(['store']);
});

/*
 * Zápisy pre prihlásených
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()))->name('api.user');

    Route::apiResources([
        'notifications'         => Api\NotificationController::class,
        'users'                 => Api\UserController::class,
        'users.comments'        => Api\User\UserCommentController::class,
        'users.organizations'   => Api\UserOrganizationController::class,
        'villages'              => Api\VillageController::class,
        'updaters'              => Api\UpdaterController::class,
    ]);

    Route::apiResource('prayers', Api\PrayerController::class)->only(['update', 'destroy']);
    Route::apiResource('comments', Api\CommentController::class)->only(['destroy']);
    Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['update', 'destroy']);

    // Zverejnenie / zablokovanie príspevku a presun do Bufferu sú akcie
    // administrácie — tlačidlá k nim sa vykresľujú len na admin.buffer.index
    // (resources/views/posts/card-front.blade.php:52).
    Route::middleware('checkSuperAdmin')->group(function () {
        Route::apiResource('posts', Api\PostController::class)->only(['update']);
        Route::apiResource('postSupport', Api\PostSupportController::class)->only(['update']);
    });
});
