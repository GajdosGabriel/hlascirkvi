<?php
use OpenAI\Laravel\Facades\OpenAI;
Auth::routes();

Route::get('/openAi', function() {
    //  $models = OpenAI::models()->list();
    //     dd($models);

    $response = OpenAI::chat()->create([
        'model' => 'gpt-4.1-mini',
        'messages' => [
            ['role' => 'user', 'content' => 'Napíš krátky pozdrav']
        ],

      'response_format' => [
        'type' => 'json_schema',
        'json_schema' => [
            'name' => 'event_extraction',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'start_date' => [
                        'type' => 'string',
                        'description' => 'Dátum začiatku akcie vo formáte YYYY-MM-DD'
                    ],
                    'organizer' => [
                        'type' => 'string'
                    ],
                    'meeting_place' => [
                        'type' => 'string'
                    ],
                ],
                'required' => ['start_date', 'organizer', 'meeting_place'],
                'additionalProperties' => false
            ]
        ]
    ],
]);

    dd($response->choices[0]->message->content);
});

Route::get('/', 'Public\PostController@index')->name('posts.index');

// Mapa webu pre vyhľadávače. /sitemap.xml je rozcestník, samotné adresy sú
// kvôli počtu príspevkov rozdelené do dávok (App\Http\Controllers\Public\
// SitemapController). Odkaz na ňu nesie aj public/robots.txt.
Route::get('sitemap.xml', 'Public\SitemapController@index')->name('sitemap');
Route::get('sitemap-stranky.xml', 'Public\SitemapController@pages')->name('sitemap.pages');
Route::get('sitemap-kanaly.xml', 'Public\SitemapController@organizations')->name('sitemap.organizations');
Route::get('sitemap-prispevky-{page}.xml', 'Public\SitemapController@posts')
    ->whereNumber('page')
    ->name('sitemap.posts');

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

// Podujatia na /akcie sa ťahajú z portálu event.hlascirkvi.sk (App\Services\
// EventPortal). Lokálna tabuľka `events` a celá agenda okolo nej (zakladanie,
// prihlasovanie, admin) bola zrušená, takže toto sú jediné routy podujatí.
Route::middleware('checkBanned')->group(function () {
    Route::get('akcie', 'Public\EventPortalController@index')->name('akcie.index');

    Route::get('akcie/{event}/{slug?}', 'Public\EventPortalController@show')
        ->where('event', '[0-9]+')
        ->name('event.show');
});

// Front routes
Route::middleware('checkBanned')->group(function () {
    Route::resources([
        'favorites'             => FavoriteController::class,
        'organizations'         => Public\OrganizationController::class,
        'seminars'              => Seminars\SeminarController::class,
        'seminars.posts'        => Seminars\SeminarPostController::class,
        'userSupport'           => UserSupportController::class,
        'modlitby'              => Public\PrayerController::class,
    ]);
});

Route::name('profile.')->middleware(['auth', 'checkBanned'])->group(function () {
    Route::resources([
        'images'                        => ImageController::class,
        'organization.seminar'          => Organization\OrganizationSeminarController::class,
        'organization.post'             => Organization\OrganizationPostController::class,
        'organization.prayer'           => Organization\OrganizationPrayerController::class,
        'profile'                       => Organization\ProfileController::class,
        'user.organization'             => User\UserOrganizationController::class,
        'post.think'                    => PostThingController::class,
    ]);

    // UserAddressController only imports contacts, it has no create/show/edit/
    // update/destroy actions - registering them would just 500.
    Route::resource('user.address', User\UserAddressController::class)->only(['index', 'store']);
});


Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkSuperAdmin', 'checkBanned'])->group(function () {
    Route::resources([
        'home'                 => Admin\AdminController::class,
        'buffer'               => Admin\BufferController::class,
        'post'                 => Admin\PostController::class,
        'prayer'               => Admin\PrayerController::class,
        'comment'              => Admin\CommentController::class,
        'user'                 => Admin\UserController::class,
        'organization'         => Admin\OrganizationController::class,
        'image'                 => Admin\ImageController::class,
        'statistic'             => Admin\StatisticController::class,
        'tag'                  => Admin\TagController::class,
        'updater'              => Admin\UpdaterController::class,
        'updater.organization'  => Admin\UpdaterOrganizationController::class,
    ]);
});

Route::get('prayer/fulfilled_at/{prayer}', 'Public\PrayerController@fulfilledAt')->name('prayer.fulfilledAt');
Route::get('seminars/{seminar}/upload', 'Seminars\SeminarController@uploadVideosfromPlaylist')->name('seminars.uploadVideos');



Route::get('/user/{user}/confirmEmail/confirmEmail', 'UserSupportController@confirmEmail')->name('confirmEmail');



// Route::put('notifications/{notification}', 'NotificationController@update')->name('notification.update');


Route::middleware('bannedOrganization')->group(function () {
    // Musí stáť pred post/{post}/{slug}, inak by ju pohltil zápis detailu.
    Route::get('post/{post}/kanal/dalsie', 'Public\PostController@rail')->name('post.rail');
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



Route::post('store/message', 'MessengerController@toAdmin')->name('messengers.store');


Auth::routes();
