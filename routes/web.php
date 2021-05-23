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

Route::resources([
    'modlitby'          => PrayerController::class,
    'notifications'     => NotificationController::class,
    'registredUsers'    => RegistredUsersController::class,
    'organizations'     => OrganizationsController::class,
    'akcie'             => EventsController::class,
    'posts'             => PostsController::class,
    'comments'          => CommentsController::class,
    'favorites'         => FavoritesController::class,
]);

Route::get('prayer/fulfilled_at/{prayer}', 'PrayerController@fulfilledAt')->name('prayer.fulfilledAt');


//    Route::get('/users', 'UsersController@index')->name('users.index');

Route::get('/user/{user}/{slug}/import', 'AddresBookController@importContacts')->name('addresBook.importContacts');
Route::get('/user/{user}/confirmEmail/confirmEmail', 'UsersController@confirmEmail')->name('confirmEmail');

Route::post('user/import/{user}', 'AddresBookController@storeUsersContact')->name('addresBook.storeUsersContact');



// Route::put('notifications/{notification}', 'NotificationController@update')->name('notification.update');


Route::prefix('user/')->name('organization.')->group(function () {
    Route::get('{organization}/{slug}', 'OrganizationsController@show')->name('show');
    Route::get('{user}/{slug}/posts', 'OrganizationsController@organizationPosts')->name('posts');

    Route::middleware(['auth'])->group(function () {
        Route::get('{user}/{slug}/index', 'OrganizationsController@organizationIndex')->name('index');
        Route::get('{organization}/{slug}/profile', 'OrganizationsController@profile')->name('profile');

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
Route::get('/youtube/{user}/{slug}/search', 'YoutubeController@searchAndSaveUser')->name('youtube.searchAndSaveUser');
Route::get('/youtube/{organization}/{slug}/search', 'YoutubeController@searchAndSaveOrganization')->name('youtube.searchAndSaveOrganization');
Route::get('/youtube/{user}/{channelId}/getvideo', 'YoutubeController@getNewVideoByChannel')->name('youtube.getNewVideoByChannel');

Route::get('/online-prenosy', 'HomeController@zivePrenosy')->name('online-prenosy');
Route::get('/konferencie-a-pute', 'HomeController@konferencieApute')->name('konferencie.pute');
Route::get('/zdravie-z-bozej-ruky', 'HomeController@zdravie')->name('zdravie');


//    Event
Route::prefix('akcie/')->name('event.')->group(function () {
    Route::get('{event}/{title}', 'EventsController@show')->name('show');
    Route::post('{event}/form/subscribe', 'EventSubscribesController@subscribeByForm')->name('subscribeByForm');

    Route::middleware('auth')->group(function () {
        Route::get('{event}/{slug}/admin', 'EventsController@adminEvent')->name('admin');
        Route::get('{event}/{user}/{slug}/print', 'EventsController@printGdpr')->name('gdpr');
        Route::put('{event}/{slug}/eventInfoPanel', 'EventsController@eventInfoPanel')->name('eventInfoPanel');
        Route::get('event/subscribes/eventsubscribe/{eventSubscribe}', 'EventSubscribesController@confirmedSubscribtion')->name('disabled');
        Route::get('{event}/record/subscribe', 'FavoritesController@storeEventsRecords')->name('record');
    });

});


Route::get('storage/{filepath?}', 'EventsController@download')->name('events.download');
//Route::post('akcia/{event}/subscriptions', 'EventSubscriptionController@store')->name('event.subscriptions');
//Route::post('akcia/{event}/records', 'EventRecordsController@store')->name('records.store');


Route::post('bigThink/post/{post}/{slug}', 'BigThinksController@store')->name('bigThink.store');
Route::post('store/message', 'MessengersController@toAdmin')->name('messengers.store');


Route::get('users/{user}/favorites/user', 'FavoritesController@favoriteUsers')->name('favorites.users');



Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkAdmin'])->namespace('Admin')->group(function () {
    Route::get('home', 'AdminsController@home')->name('home');
    Route::get('index', 'AdminsController@indexOrganization')->name('organization.index');
    Route::get('index/user', 'AdminsController@indexUser')->name('user.index');
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
Route::get('akcia/finished', 'EventsController@finished')->name('event.finished');

