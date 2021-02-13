<?php


//
//Route::get('mail', function () {
//    $posts = \App\Post::latest()->take(20)->get();
//
//    return new App\Mail\PostNewsletter($posts);
//
//});

Auth::routes();

Route::get('/', 'PostsController@index')->name('posts.index');


//Route::get('/', 'HomeController@index')->name('home');

// oAuth Routes...
Route::get('/auth/{service}', 'Auth\AuthController@redirectToProvider')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('/auth/{service}/callback', 'Auth\AuthController@handleProviderCallback')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)');

Route::get('zamyslenia/{slug?}' , 'VersesController@index')->name('verses.index');

Route::resources([
    'prayers' => PrayerController::class
]);



Route::prefix('user/')->name('organization.')->group(function () {
    Route::get('{organization}/{slug}', 'UsersController@show')->name('show');
    Route::get('{user}/{slug}/posts', 'OrganizationsController@organizationPosts')->name('posts');

    Route::middleware(['auth'])->group(function () {
        Route::get('{user}/{slug}/index', 'OrganizationsController@organizationIndex')->name('index');
        Route::get('{organization}/{slug}/profile', 'UsersController@profile')->name('profile');
        Route::get('{organization}/{slug}/edit', 'OrganizationsController@edit')->name('edit');
        Route::get('{organization}/{slug}/delete', 'OrganizationsController@delete')->name('delete');
        Route::get('{organization}/favorites/subscribe', 'FavoritesController@favoriteOrganizations');

        Route::put('update/{organization}', 'OrganizationsController@update')->name('update');
        Route::post('new/{user}/store', 'OrganizationsController@store')->name('store');
        Route::post('message/{organization}/newMessage', 'MessengersController@store')->name('messengers.store.users');

    });
});

Route::prefix('post/')->name('post.')->group(function () {
    Route::get('{post}/{slug}', 'PostsController@show')->name('show');

    Route::middleware(['auth'])->group(function () {
        Route::get('{videoId}', 'PostsController@showVideo')->name('showVideo');
        Route::get('create/new/post', 'PostsController@create')->name('create');
        Route::get('edit/{post}/{slug}', 'PostsController@edit')->name('edit');
        Route::get('unpublished/{post}/video', 'PostsController@toBuffer')->name('toBuffer');
        Route::post('update/{post}', 'PostsController@update')->name('update');
        Route::post('store', 'PostsController@store')->name('store');
        Route::get('favorites/{post}/add', 'FavoritesController@favoritePosts')->name('favorites');
        Route::get('delete/{post}/softdelete', 'PostsController@delete')->name('delete');

    });
});



//    Route::get('/users', 'UsersController@index')->name('users.index');

Route::get('/user/{user}/{slug}/import', 'AddresBookController@importContacts')->name('addresBook.importContacts');


Route::get('/user/profiles/notification/unread', 'UsersController@userNotifications')->name('unread.notification');
Route::get('/user/profiles/{id}/markAsRead', 'UsersController@markAsRead')->name('unread.markAsRead');
Route::post('user/import/{user}', 'AddresBookController@storeUsersContact')->name('addresBook.storeUsersContact');

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
    Route::get('/', 'EventsController@index')->name('index');
    Route::get('{event}/{title}', 'EventsController@show')->name('show');
    Route::post('{event}/{slug}/newComment', 'CommentsController@storeEvent');
    Route::post('{event}/form/subscribe', 'EventSubscribesController@subscribeByForm')->name('subscribeByForm');

    Route::middleware('auth')->group(function () {
        Route::get('create', 'EventsController@create')->name('create');
        Route::get('{event}/{title}/edit', 'EventsController@edit')->name('edit');
        Route::patch('{event}/{slug}/eventupdate', 'EventsController@update')->name('update');
        Route::post('store', 'EventsController@store')->name('store');
        Route::get('{event}/delete/soft', 'EventsController@delete')->name('delete');
        Route::get('{event}/{slug}/admin', 'EventsController@adminEvent')->name('admin');
        Route::get('{event}/{user}/{slug}/print', 'EventsController@printGdpr')->name('gdpr');
        Route::put('{event}/{slug}/eventInfoPanel', 'EventsController@eventInfoPanel')->name('eventInfoPanel');
        Route::get('{event}/subscribes/eventsubscribe', 'EventsController@subcribeToEvent')->name('subcribeToEvent');
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


    // Comments
Route::prefix('comment/')->name('comment.')->group(function () {
    Route::post('post/{post}/{slug}', 'CommentsController@store')->name('store');

    Route::middleware('auth')->group(function () {
    Route::put('{comment}/update/comment', 'CommentsController@updateComment')->name('update');
    Route::get('{comment}/delete/comment', 'CommentsController@destroyComment')->name('destroy');
    Route::get('{comment}/favorites/comment', 'FavoritesController@favoriteComments');

    });
});



Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkAdmin'])->namespace('Admin')->group(function () {
    Route::get('home', 'AdminsController@home')->name('home');
    Route::get('index', 'AdminsController@indexOrganization')->name('organization.index');
    Route::get('index/user', 'AdminsController@indexUser')->name('user.index');
    Route::get('buffered/videos', 'BuffersController@indexBufferedVideos')->name('unpublished');
    Route::get('publish/buffer/{post}', 'BuffersController@bufferedVideosPublish')->name('bufferedVideosPublish');
    Route::get('statistic/{days}', 'AdminsController@statistic')->name('statistic');
    Route::get('youtubeBlocked/{post}/blocked', 'BuffersController@youtubeBlocked')->name('youtubeBlocked');

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
