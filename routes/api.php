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

// Meno `posts.index` už patrí verejnému výpisu (routes/web.php:63). Bez
// premenovania ho tento zdroj prebije a `route('posts.index')` v šablónach
// vráti /api/posts — prepínače nad výpisom potom namiesto stránky otvárali JSON.
Route::apiResource('posts', Api\PostController::class)
    ->only(['index'])
    ->names(['index' => 'api.posts.index']);

Route::apiResource('comments', Api\CommentController::class)->only(['index']);
Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['index']);
// Adresa /api/organization/{id} ostáva — volá ju Vue na verejnej stránke
// kanála. Parameter je {canal} kvôli implicitnej väzbe na Canal $canal.
Route::apiResource('organization', Api\CanalController::class)
    ->only(['show'])
    ->parameters(['organization' => 'canal']);

Route::get('rss-reader-canal/{canal}', 'Api\RssController@getRssCanal')
    ->whereIn('canal', ['domov', 'zahranicie', 'press'])
    ->name('api.rss');

/*
 * Verejné zápisy — anonymný komentár a anonymné označenie obľúbeného kanála
 * sú funkcia webu, nie diera. Sú preto obmedzené sadzbou.
 */
Route::middleware('throttle:10,1')->group(function () {
    Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['store']);
    Route::apiResource('organizations.favorites', Api\CanalFavoriteController::class)
        ->only(['store'])
        ->parameters(['organizations' => 'canal']);

    // Modlitbu vie pridať aj neprihlásený — formulár od neho žiada e-mail
    // (resources/js/prayer/ModalNewPrayer.vue:103) a EloquentUserRepository
    // ::checkIfUserAccountExist mu podľa neho založí a prihlási účet.
    Route::apiResource('prayers', Api\PrayerController::class)->only(['store']);
});

/*
 * Zápisy pre prihlásených
 */
Route::middleware(['auth:sanctum', 'checkBanned'])->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()))->name('api.user');

    // Zvonček v navigácii: zoznam, prečítané/neprečítané, mazanie jednej
    // položky aj hromadné akcie. Hromadné cesty stoja pred zdrojom, aby ich
    // nepohltilo /notifications/{notification}.
    Route::post('notifications/read', 'Api\NotificationController@markRead')->name('notifications.read');
    Route::post('notifications/unread', 'Api\NotificationController@markUnread')->name('notifications.unread');
    Route::delete('notifications', 'Api\NotificationController@destroyAll')->name('notifications.destroyAll');
    Route::apiResource('notifications', Api\NotificationController::class)->only(['index', 'update', 'destroy']);
    Route::apiResource('users', Api\UserController::class)->only('update');
    Route::apiResource('users.comments', Api\User\UserCommentController::class)->only('index');
    Route::apiResource('users.organizations', Api\UserCanalController::class)
        ->only('store')
        ->parameters(['organizations' => 'canal']);
    Route::apiResource('villages', Api\VillageController::class)->only(['index', 'store', 'show']);
    Route::apiResource('denominations', Api\DenominationController::class)->only('index');

    Route::apiResource('prayers', Api\PrayerController::class)->only(['update', 'destroy']);
    Route::apiResource('comments', Api\CommentController::class)->only(['destroy']);
    Route::post('comments/{comment}/like', 'Api\CommentLikeController@store')
        ->middleware('throttle:60,1')
        ->name('comments.like');
    Route::apiResource('posts.comments', Api\PostCommentController::class)->only(['update', 'destroy']);

    // Zverejnenie / zablokovanie príspevku a presun do Bufferu sú akcie
    // administrácie — tlačidlá k nim sa vykresľujú len na admin.buffer.index
    // (resources/views/posts/card-front.blade.php:55).
    Route::middleware('checkSuperAdmin')->group(function () {
        Route::apiResource('posts', Api\PostController::class)->only(['update']);
        Route::apiResource('postSupport', Api\PostSupportController::class)->only(['update']);
    });
});
