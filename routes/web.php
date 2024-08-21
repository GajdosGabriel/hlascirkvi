<?php

Auth::routes();

Route::get('/', 'Public\PostController@index')->name('posts.index');

Route::get('/gdpr', 'Public\HomeController@gdpr')->name('gdpr');
Route::get('/online-prenosy', 'Public\HomeController@zivePrenosy')->name('online-prenosy');
Route::get('/konferencie-a-pute', 'Public\HomeController@seminare')->name('konferencie.pute');
Route::get('/zdravie-z-bozej-ruky', 'Public\HomeController@zdravie')->name('zdravie');


// oAuth Routes...
Route::get('/auth/{service}', 'Auth\AuthController@redirectToProvider')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('/auth/{service}/callback', 'Auth\AuthController@handleProviderCallback')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('zamyslenia/{slug?}', 'VerseController@index')->name('verses.index');

// Front routes
Route::middleware('checkBanned')->group(function () {
    Route::resources([
        'akcie'                 => Public\EventController::class,
        'favorites'             => FavoriteController::class,
        'organizations'         => Public\OrganizationController::class,
        'seminars'              => Seminars\SeminarController::class,
        'seminars.posts'        => Seminars\SeminarPostController::class,
        'userSupport'           => UserSupportController::class,
        'modlitby'              => Public\PrayerController::class,
        'event.subscribeGuest'  => Events\EventSubscribeGuestController::class,
    ]);
});

Route::name('profile.')->middleware(['auth', 'checkBanned'])->group(function () {
    Route::resources([
        'images'                        => ImageController::class,
        'event.subscribe'               => Events\EventSubscribeController::class,
        'event.favorite'                => Events\EventFavoriteController::class,
        'organization.seminar'          => Organization\OrganizationSeminarController::class,
        'post'             => Profile\PostController::class,
        'organization.event'            => Organization\OrganizationEventController::class,
        'organization.prayer'           => Organization\OrganizationPrayerController::class,
        'organization.eventSubscribe'   => Organization\OrganizationEventSubscribeController::class,
        'profile'                       => Organization\ProfileController::class,
        'user.organization'             => Organization\UserOrganizationController::class,
        'user.address'                  => User\UserAddressController::class,
        'post.think'                    => PostThingController::class,
    ]);
});


Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkSuperAdmin', 'checkBanned'])->group(function () {
    Route::resources([
        'home'                  => Admin\AdminController::class,
        'buffer'               => Admin\BufferController::class,
        'post'                 => Admin\PostController::class,
        'prayer'               => Admin\PrayerController::class,
        'event'                => Admin\EventController::class,
        'comment'              => Admin\CommentController::class,
        'user'                 => Admin\UserController::class,
        'organization'         => Admin\OrganizationController::class,
        'image'                 => Admin\ImageController::class,
        'statistic'             => Admin\StatisticController::class,
        'tag'                  => Admin\TagController::class,
        'updater'              => Admin\UpdaterController::class,
        'updater.organization'  => Admin\UpdaterOrganizationController::class,
        'eventSubscribe'       => Admin\EventSubscribeController::class,
    ]);
});

Route::get('prayer/fulfilled_at/{prayer}', 'PrayerController@fulfilledAt')->name('prayer.fulfilledAt');
Route::get('seminars/{seminar}/upload', 'Seminars\SeminarController@uploadVideosfromPlaylist')->name('seminars.uploadVideos');



Route::get('/user/{user}/confirmEmail/confirmEmail', 'UserSupportController@confirmEmail')->name('confirmEmail');



// Route::put('notifications/{notification}', 'NotificationController@update')->name('notification.update');


Route::middleware('bannedOrganization')->group(function () {
    Route::get('post/{post}/{slug}', 'Public\PostController@show')->name('post.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/search/new/video/{user}', 'YoutubeController@searchUserVideo')->name('videos.searchUserVideo');
    Route::get('/search/new/video/{organization}', 'YoutubeController@searchOrganizationVideo')->name('videos.searchOrganizationVideo');
    Route::get('/get/video/byId/{id}', 'YoutubeController@getVideoById')->name('videos.getVideoById');
    Route::get('/youtube/{user}/{slug}/search', 'YoutubeController@searchAndSaveUser')->name('youtube.searchAndSaveUser');
    Route::get('/youtube/{organization}/{slug}/search', 'YoutubeController@searchAndSaveOrganization')->name('youtube.searchAndSaveOrganization');
    Route::get('/youtube/{user}/{channelId}/getvideo', 'YoutubeController@getNewVideoByChannel')->name('youtube.getNewVideoByChannel');
});



// Event
Route::prefix('akcie/')->name('event.')->group(function () {
    Route::get('{event}/{title}', 'Public\EventController@show')->name('show');
    Route::post('{event}/form/subscribe', 'EventSubscribeController@subscribeByForm')->name('subscribeByForm');

    Route::middleware('auth')->group(function () {
        Route::get('{event}/{user}/{slug}/print', 'Events\EventController@printGdpr')->name('gdpr');
        Route::put('{event}/{slug}/eventInfoPanel', 'Events\EventController@eventInfoPanel')->name('eventInfoPanel');
    });
});


Route::get('storage/{filepath?}', 'Events\EventController@download')->name('events.download');


Route::post('store/message', 'MessengerController@toAdmin')->name('messengers.store');


Auth::routes();
