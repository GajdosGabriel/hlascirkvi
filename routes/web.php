<?php
// Prihlasovacie routy stoja explicitne, nie cez macro Auth::routes() z
// laravel/ui. Kontrolery v App\Http\Controllers\Auth pritom stále stoja na
// traitoch Illuminate\Foundation\Auth\* (AuthenticatesUsers, RegistersUsers,
// ResetsPasswords...), ktoré od Laravelu 8 nie sú vo frameworku a dodáva ich
// práve laravel/ui cez vendor/laravel/ui/auth-backend. Balík preto musí zostať
// v "require", nie v "require-dev" - produkčné composer install --no-dev inak
// zhodí celé prihlasovanie.
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');

// Overenie e-mailu. `verification.verify` zámerne nie je za `auth` — odkaz
// z pošty sa otvára aj v inom prehliadači, než v ktorom človek registroval.
// Autorizáciu nesie podpis v URL, middleware si dopĺňa samotný controller.
Route::get('email/verify', 'Auth\VerificationController@notice')->name('verification.notice');
Route::get('email/verify/{user}/{hash}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::post('email/verify/resend', 'Auth\VerificationController@resend')->name('verification.resend');

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

// Celý predný zoznam kanálov. Karta v bočnom paneli ukazuje len prvých pár
// (config frontlist.card_limit) a odkazuje sem.
Route::get('/osobnosti', 'Public\FrontListController@index')->name('frontlist.index');


// oAuth Routes...
Route::get('/auth/{service}', 'Auth\AuthController@redirectToProvider')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)')
    ->name('auth.redirect');

Route::get('/auth/{service}/callback', 'Auth\AuthController@handleProviderCallback')
    ->where('service', '(github|facebook|google|twitter|linkedin|bitbucket)')
    ->name('auth.callback');

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
        'organizations'         => Public\CanalController::class,
        'seminars'              => Seminars\SeminarController::class,
        'seminars.posts'        => Seminars\SeminarPostController::class,
        'userSupport'           => UserSupportController::class,
        'modlitby'              => Public\PrayerController::class,
    ]);
});

// Meno skupiny musí ostať `profile.` — helper typePage() (app/Http/helpers.php)
// podľa neho pozná stránky správcu kanála a vykresľuje im bočné menu.
Route::name('profile.')->middleware(['auth', 'checkBanned'])->group(function () {
    // Nástenka kanála. Resource z nej nikdy nemal viac ako index, preto je to
    // jediná routa.
    Route::get('dashboard', Canal\DashboardController::class)->name('dashboard');

    // ImageController vie jediné — zmazať obrázok (výpisy aj nahrávanie sú
    // súčasťou formulárov článku a kanála). Zvyšok resource by len padol.
    Route::resource('images', ImageController::class)->only('destroy');

    // Správa kanálov prihláseného užívateľa. Užívateľ sa berie z prihlásenia;
    // kým bol v adrese (/user/{user}/organization), musela ho každá akcia
    // porovnávať s auth()->id().
    Route::prefix('dashboard')->group(function () {
        // Prepnutie aktívneho kanála z výpisu "Vaše kanály". Nie je to update
        // kanála ani užívateľa, preto vlastná routa; autorizuje ju policy `manage`.
        Route::put('canals/{canal}/switch', 'Canal\CanalController@switchActive')->name('canals.switch');

        // Kanál sa z nástenky nemaže, destroy by len spadol.
        Route::resource('canals', Canal\CanalController::class)->except('destroy');

        // Články aktívneho kanála (users.org_id) — rovnako ako nástenka nemajú
        // kanál v adrese. Prepína sa výpisom "Vaše kanály". Detail článku je
        // verejný (post.show), show tu nikdy nebol.
        Route::resource('posts', Canal\CanalPostController::class)->except('show');

        // Modlitba sa zo správy kanála len zakladá a upravuje — detail
        // (show) kontroler nemá, registrovaná routa by skončila 500-kou.
        Route::resource('canals.prayers', Canal\CanalPrayerController::class)->except('show');
        Route::resource('canals.seminars', Canal\CanalSeminarController::class);
    });

    // UserAddressController only imports contacts, it has no create/show/edit/
    // update/destroy actions - registering them would just 500.
    Route::resource('user.address', User\UserAddressController::class)->only(['index', 'store']);
});

// Pôvodná adresa nástenky. Rozposlaná v e-mailoch aj v záložkách správcov
// kanálov, takže ostáva ako trvalé presmerovanie.
Route::permanentRedirect('profile', 'dashboard');

// Adresy správy kanála spred 9/2026 (/user/{user}/organization/...,
// /organization/{id}/post/...). Môžu byť v záložkách a e-mailoch; formuláre sa
// už vykresľujú s novými adresami.
Route::permanentRedirect('user/{user}/organization/{rest?}', '/dashboard/canals/{rest?}')
    ->where('rest', '.*');
Route::permanentRedirect('organization/{canal}/post/{rest?}', '/dashboard/posts/{rest?}')
    ->where('rest', '.*');
// Články kanála mali do 10. 9. 2026 kanál v adrese (/dashboard/canals/{id}/posts).
Route::permanentRedirect('dashboard/canals/{canal}/posts/{rest?}', '/dashboard/posts/{rest?}')
    ->where('rest', '.*');
Route::permanentRedirect('organization/{canal}/prayer/{rest?}', '/dashboard/canals/{canal}/prayers/{rest?}')
    ->where('rest', '.*');
Route::permanentRedirect('organization/{canal}/seminar/{rest?}', '/dashboard/canals/{canal}/seminars/{rest?}')
    ->where('rest', '.*');


Route::prefix('admin/')->name('admin.')->middleware(['auth', 'checkSuperAdmin', 'checkBanned'])->group(function () {
    Route::get('canal', 'Admin\CanalController@index')->name('canal.index');
    Route::permanentRedirect('organization', '/admin/canal');

    // Zapnutie a vypnutie oznamu priamo z výpisu. Nie je to úprava oznamu,
    // preto vlastná routa a nie update s celým formulárom.
    Route::put('announcement/{announcement}/toggle', 'Admin\AnnouncementController@toggle')
        ->name('announcement.toggle');

    // Oznam nemá verejný detail — upravuje sa vo formulári, zobrazuje sa na webe.
    Route::resource('announcement', Admin\AnnouncementController::class)->except('show');

    // Predný zoznam kanálov na úvodnej stránke. Nie je to CRUD nad vlastným
    // modelom — zaradenie a poradie sú stĺpce kanála, preto vlastné routy.
    Route::get('front-list', 'Admin\FrontListController@index')->name('frontlist.index');
    Route::post('front-list', 'Admin\FrontListController@store')->name('frontlist.store');
    Route::put('front-list/{canal}/move', 'Admin\FrontListController@move')->name('frontlist.move');
    Route::delete('front-list/{canal}', 'Admin\FrontListController@destroy')->name('frontlist.destroy');

    /*
     * Administrácia je zväčša len výpis. Celý resource tu registroval sedem
     * rout na kontroler, ktorý má jedinú metódu — /admin/post/create,
     * /admin/user/{id} či /admin/comment/{id}/edit tak každému, kto na ne
     * trafil, vrátili 500 ("Method ... does not exist"). Rovnaké pravidlo
     * ako pri user.address nižšie: registruje sa len to, čo kontroler vie.
     */
    Route::resource('home', Admin\AdminController::class)->only('index');
    Route::resource('buffer', Admin\BufferController::class)->only('index');
    Route::resource('post', Admin\PostController::class)->only('index');
    Route::resource('prayer', Admin\PrayerController::class)->only('index');
    Route::resource('comment', Admin\CommentController::class)->only('index');
    Route::resource('statistic', Admin\StatisticController::class)->only('index');
    Route::resource('user', Admin\UserController::class)->only(['index', 'edit', 'update']);
    Route::resource('image', Admin\ImageController::class)->only(['index', 'destroy']);
    Route::resource('tag', Admin\TagController::class)->only(['index', 'store', 'destroy']);
});

// Obe tieto routy sú odkazy z e-mailu, takže musia zostať GET. Autorizáciu
// nesie podpis v URL (URL::signedRoute v notifikácii) — bez neho stačilo
// uhádnuť ID a označiť cudziu modlitbu za vypočutú, resp. overiť cudzí e-mail.
Route::get('prayer/fulfilled_at/{prayer}', 'Public\PrayerController@fulfilledAt')
    ->middleware('signed')
    ->name('prayer.fulfilledAt');

Route::get('/user/{user}/confirmEmail/confirmEmail', 'UserSupportController@confirmEmail')
    ->middleware('signed')
    ->name('confirmEmail');

// Import videí z YouTube playlistu je dlhá externá operácia, ktorá zapisuje —
// preto POST za prihlásením, nie GET. Vlastníctvo seminára overuje controller.
Route::post('seminars/{seminar}/upload', 'Seminars\SeminarController@uploadVideosfromPlaylist')
    ->middleware('auth')
    ->name('seminars.uploadVideos');


Route::middleware('bannedCanal')->group(function () {
    // Musí stáť pred post/{post}/{slug}, inak by ju pohltil zápis detailu.
    Route::get('post/{post}/kanal/dalsie', 'Public\PostController@rail')->name('post.rail');
    // Slug je nepovinný: názvy bez písmen latinky (emoji, interpunkcia) dajú
    // prázdny Str::slug a príspevku sa potom nedala zostaviť adresa — route()
    // nechal {slug} nenahradený a zhodil celý výpis kariet.
    Route::get('post/{post}/{slug?}', 'Public\PostController@show')->name('post.show');
});

// Routy pre kanál musia mať vlastný prefix. Kým mali rovnaký tvar ako tie
// užívateľské (/search/new/video/{param}), router vždy vybral prvú z dvojice
// a organizačné akcie boli nedosiahnuteľné.
Route::middleware('auth')->group(function () {
    Route::get('/search/new/video/user/{user}', 'YoutubeController@searchUserVideo')->name('videos.searchUserVideo');
    Route::get('/search/new/video/organization/{organization}', 'YoutubeController@searchOrganizationVideo')->name('videos.searchOrganizationVideo');
    Route::get('/get/video/byId/{id}', 'YoutubeController@getVideoById')->name('videos.getVideoById');
    Route::get('/youtube/user/{user}/{slug}/search', 'YoutubeController@searchAndSaveUser')->name('youtube.searchAndSaveUser');
    Route::get('/youtube/organization/{organization}/{slug}/search', 'YoutubeController@searchAndSaveOrganization')->name('youtube.searchAndSaveOrganization');
    Route::get('/youtube/{user}/{channelId}/getvideo', 'YoutubeController@getNewVideoByChannel')->name('youtube.getNewVideoByChannel');
});


Route::post('store/message', 'MessengerController@toAdmin')->name('messengers.store');
