<?php



Auth::routes();

Route::get('/', 'PostsController@index')->name('posts.index');


//Route::get('/', 'HomeController@index')->name('home');

// oAuth Routes...
Route::get('/auth/{service}', 'Auth\AuthController@redirectToProvider')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('/auth/{service}/callback', 'Auth\AuthController@handleProviderCallback')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('zamyslenia/{slug?}', 'VersesController@index')->name('verses.index');

// Front routes
Route::resources([
    'akcie'                 => Events\EventsController::class,
    'favorites'             => FavoritesController::class,
    'organizations'         => OrganizationsController::class,
    'posts'                 => PostsController::class,
    'seminars'              => Seminars\SeminarsController::class,
    'seminars.posts'        => Seminars\SeminarPostController::class,
    'user'                  => UsersController::class,
    'modlitby'              => PrayerController::class,
]);

Route::middleware(['middleware' => 'auth'])->group(function () {
    Route::resources([
        'images'                => ImagesController::class,
        'organization.seminar'  => OrganizationSeminarController::class,
        'organization.post'     => OrganizationPostController::class,
        'organization.event'    => OrganizationEventController::class,
        'user.prayer'           => UserPrayerController::class,
        'profile'               => ProfilesController::class,
        'tags'                  => TagsController::class,
        'updaters'              => Updaters\UpdatersController::class,
        'updater.organization'  => Updaters\UpdaterOrganizationController::class,
        'user.organization'     => UserOrganizationController::class,
    ]);
});

Route::get('prayer/fulfilled_at/{prayer}', 'PrayerController@fulfilledAt')->name('prayer.fulfilledAt');
Route::get('seminars/{seminar}/upload', 'Seminars\SeminarsController@uploadVideosfromPlaylist')->name('seminars.uploadVideos');


//    Route::get('/users', 'UsersController@index')->name('users.index');

Route::get('/user/{user}/{slug}/import', 'AddresBookController@importContacts')->name('addresBook.importContacts');
Route::get('/user/{user}/confirmEmail/confirmEmail', 'UsersController@confirmEmail')->name('confirmEmail');

Route::post('user/import/{user}', 'AddresBookController@storeUsersContact')->name('addresBook.storeUsersContact');



// Route::put('notifications/{notification}', 'NotificationController@update')->name('notification.update');


Route::prefix('user/')->name('organization.')->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::post('message/{organization}/newMessage', 'MessengersController@store')->name('messengers.store.users');
    });
});

Route::prefix('post/')->name('post.')->group(function () {
    Route::get('{post}/{slug}', 'PostsController@show')->name('show');

    Route::middleware(['auth'])->group(function () {
        Route::get('{videoId}', 'PostsController@showVideo')->name('showVideo');
        Route::get('unpublished/{post}/video', 'PostsController@toBuffer')->name('toBuffer');
    });
});



Route::post('account/avatar/store', 'Account\AvatarController@store')->name('account.avatar.store');


Route::get('/search/new/video/{user}', 'YoutubeController@searchUserVideo')->name('videos.searchUserVideo');
Route::get('/search/new/video/{organization}', 'YoutubeController@searchOrganizationVideo')->name('videos.searchOrganizationVideo');
Route::get('/get/video/byId/{id}', 'YoutubeController@getVideoById')->name('videos.getVideoById');
Route::get('/youtube/{user}/{slug}/search', 'YoutubeController@searchAndSaveUser')->name('youtube.searchAndSaveUser');
Route::get('/youtube/{organization}/{slug}/search', 'YoutubeController@searchAndSaveOrganization')->name('youtube.searchAndSaveOrganization');
Route::get('/youtube/{user}/{channelId}/getvideo', 'YoutubeController@getNewVideoByChannel')->name('youtube.getNewVideoByChannel');

Route::get('/online-prenosy', 'HomeController@zivePrenosy')->name('online-prenosy');
Route::get('/konferencie-a-pute', 'HomeController@seminare')->name('konferencie.pute');
Route::get('/zdravie-z-bozej-ruky', 'HomeController@zdravie')->name('zdravie');


//    Event
Route::prefix('akcie/')->name('event.')->group(function () {
    Route::get('{event}/{title}', 'Events\EventsController@show')->name('show');
    Route::post('{event}/form/subscribe', 'EventSubscribesController@subscribeByForm')->name('subscribeByForm');

    Route::middleware('auth')->group(function () {
        Route::get('{event}/{slug}/admin', 'Events\EventsController@adminEvent')->name('admin');
        Route::get('{event}/{user}/{slug}/print', 'Events\EventsController@printGdpr')->name('gdpr');
        Route::put('{event}/{slug}/eventInfoPanel', 'Events\EventsController@eventInfoPanel')->name('eventInfoPanel');
        Route::get('event/subscribes/eventsubscribe/{eventSubscribe}', 'EventSubscribesController@confirmedSubscribtion')->name('disabled');
        Route::get('{event}/record/subscribe', 'FavoritesController@storeEventsRecords')->name('record');
    });
});


Route::get('storage/{filepath?}', 'Events\EventsController@download')->name('events.download');
//Route::post('akcia/{event}/subscriptions', 'EventSubscriptionController@store')->name('event.subscriptions');
//Route::post('akcia/{event}/records', 'EventRecordsController@store')->name('records.store');


Route::post('bigThink/post/{post}/{slug}', 'BigThinksController@store')->name('bigThink.store');
Route::post('store/message', 'MessengersController@toAdmin')->name('messengers.store');


Route::get('users/{user}/favorites/user', 'FavoritesController@favoriteUsers')->name('favorites.users');



Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkSuperAdmin'])->namespace('Admin')->group(function () {
    Route::get('home', 'AdminsController@home')->name('home');
    Route::get('index', 'AdminsController@organizations')->name('organizations');
    Route::get('users', 'UsersController@index')->name('users.index');
    Route::get('events', 'EventsController@index')->name('events.index');
    Route::get('prayers', 'PrayersController@index')->name('prayers.index');
    Route::get('buffered-videos', 'BuffersController@indexBufferedVideos')->name('unpublished');
    Route::get('statistic/{days}', 'AdminsController@statistic')->name('statistic');

    Route::get('images/index', 'ImagesController@index')->name('images.index');
    Route::get('images/destroy', 'ImagesController@destroy')->name('images.destroy');
});

Route::prefix('village/')->name('village.')->middleware(['auth'])->group(function () {
    Route::get('{fullname}', 'VillagesController@index')->name('index');
});
Route::post('denomination/set/session', 'UsersController@setDenominationSession')->name('user.denomination');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/gdpr', 'HomeController@gdpr')->name('gdpr');
Route::get('akcia/finished', 'Events\EventsController@finished')->name('event.finished');
