<?php

Auth::routes();

Route::get('/', 'PostController@index')->name('posts.index');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/gdpr', 'HomeController@gdpr')->name('gdpr');
Route::get('/online-prenosy', 'HomeController@zivePrenosy')->name('online-prenosy');
Route::get('/konferencie-a-pute', 'HomeController@seminare')->name('konferencie.pute');
Route::get('/zdravie-z-bozej-ruky', 'HomeController@zdravie')->name('zdravie');


// oAuth Routes...
Route::get('/auth/{service}', 'Auth\AuthController@redirectToProvider')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('/auth/{service}/callback', 'Auth\AuthController@handleProviderCallback')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('zamyslenia/{slug?}', 'VerseController@index')->name('verses.index');

// Front routes
Route::middleware('checkBanned')->group(function () {
    Route::resources([
        'akcie'                 => Events\EventController::class,
        'favorites'             => FavoriteController::class,
        'organizations'         => OrganizationController::class,
        'posts'                 => PostController::class,
        'seminars'              => Seminars\SeminarController::class,
        'seminars.posts'        => Seminars\SeminarPostController::class,
        'userSupport'           => UserSupportController::class,
        'modlitby'              => PrayerController::class,
    ]);
});

Route::middleware(['auth', 'checkBanned'])->group(function () {
    Route::resources([
        'images'                => ImageController::class,
        'event.eventSubscribe'  => Events\EventSubscribeController::class,
        'organization.seminar'  => OrganizationSeminarController::class,
        'organization.post'     => OrganizationPostController::class,
        'organization.event'    => OrganizationEventController::class,
        'user.prayer'           => UserPrayerController::class,
        'profile'               => ProfileController::class,
        'user.organization'     => UserOrganizationController::class,
        'post.think'            => PostThingController::class,

    ]);
});


Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkSuperAdmin', 'checkBanned'])->group(function () {
    Route::resources([
        'home'                  => Admin\AdminController::class,
        'buffers'               => Admin\BufferController::class,
        'posts'                 => Admin\PostController::class,
        'prayers'               => Admin\PrayerController::class,
        'events'                => Admin\EventController::class,
        'comments'              => Admin\CommentController::class,
        'users'                 => Admin\UserController::class,
        'organizations'         => Admin\OrganizationController::class,
        'image'                 => Admin\ImageController::class,
        'statistic'             => Admin\StatisticController::class,
        'tags'                  => Admin\TagController::class,
        'updaters'              => Admin\UpdaterController::class,
        'updater.organization'  => Admin\UpdaterOrganizationController::class,
    ]);
});

Route::get('prayer/fulfilled_at/{prayer}', 'PrayerController@fulfilledAt')->name('prayer.fulfilledAt');
Route::get('seminars/{seminar}/upload', 'Seminars\SeminarController@uploadVideosfromPlaylist')->name('seminars.uploadVideos');


//    Route::get('/users', 'UserController@index')->name('users.index');

Route::get('/user/{user}/{slug}/import', 'AddresBookController@importContacts')->name('addresBook.importContacts');
Route::get('/user/{user}/confirmEmail/confirmEmail', 'UserSupportController@confirmEmail')->name('confirmEmail');

Route::post('user/import/{user}', 'AddresBookController@storeUsersContact')->name('addresBook.storeUsersContact');



// Route::put('notifications/{notification}', 'NotificationController@update')->name('notification.update');


Route::middleware('checkBanned')->group(function () {
    Route::get('post/{post}/{slug}', 'PostController@show')->name('post.show');
});


Route::get('/search/new/video/{user}', 'YoutubeController@searchUserVideo')->name('videos.searchUserVideo');
Route::get('/search/new/video/{organization}', 'YoutubeController@searchOrganizationVideo')->name('videos.searchOrganizationVideo');
Route::get('/get/video/byId/{id}', 'YoutubeController@getVideoById')->name('videos.getVideoById');
Route::get('/youtube/{user}/{slug}/search', 'YoutubeController@searchAndSaveUser')->name('youtube.searchAndSaveUser');
Route::get('/youtube/{organization}/{slug}/search', 'YoutubeController@searchAndSaveOrganization')->name('youtube.searchAndSaveOrganization');
Route::get('/youtube/{user}/{channelId}/getvideo', 'YoutubeController@getNewVideoByChannel')->name('youtube.getNewVideoByChannel');




//    Event
Route::prefix('akcie/')->name('event.')->group(function () {
    Route::get('{event}/{title}', 'Events\EventController@show')->name('show');
    Route::post('{event}/form/subscribe', 'EventSubscribeController@subscribeByForm')->name('subscribeByForm');

    Route::middleware('auth')->group(function () {
        Route::get('{event}/{user}/{slug}/print', 'Events\EventController@printGdpr')->name('gdpr');
        Route::put('{event}/{slug}/eventInfoPanel', 'Events\EventController@eventInfoPanel')->name('eventInfoPanel');
    });
});


Route::get('storage/{filepath?}', 'Events\EventController@download')->name('events.download');


Route::post('store/message', 'MessengerController@toAdmin')->name('messengers.store');


Route::get('users/{user}/favorites/user', 'FavoriteController@favoriteUsers')->name('favorites.users');


Route::prefix('village/')->name('village.')->middleware(['auth'])->group(function () {
    Route::get('{fullname}', 'VillageController@index')->name('index');
});
Route::post('denomination/set/session', 'UserController@setDenominationSession')->name('user.denomination');

Auth::routes();
