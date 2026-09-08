<?php

return [

    /*
     * Meno webu tak, ako ho má vidieť Facebook a Google. Chodí do og:site_name,
     * do prípony titulku a do schema.org Organization.
     */
    'site_name' => env('SEO_SITE_NAME', 'Hlas Cirkvi'),

    /*
     * Kanonická adresa webu. Zdieľané odkazy a sitemap musia ukazovať na jednu
     * doménu, inak Facebook aj Google zbierajú štatistiky na dve URL. Lokálny
     * vývoj si ju necháva z APP_URL, produkcia má v .env ostrú doménu.
     */
    'url' => env('SEO_URL', env('APP_URL')),

    /*
     * Titulok a popis pre stránky, ktoré si vlastný nenastavia. Popis vidno
     * vo výsledku vyhľadávania, preto je písaný ako veta pre človeka, nie ako
     * zoznam kľúčových slov.
     */
    'title' => 'Hlas Cirkvi – kázne, videá a modlitby kresťanských spoločenstiev',

    'description' => 'Kázne, prenosy bohoslužieb a videá kresťanských spoločenstiev na Slovensku. '
        . 'Modlitebný múr, denné zamyslenia a prehľad kresťanských podujatí.',

    /*
     * Náhľadový obrázok pre zdieľanie. Facebook aj X žiadajú aspoň 200 px a
     * odporúčajú 1200×630; menší obrázok zahodia a odkaz vykreslia holý.
     * Rozmery sú tu zámerne uvedené — bez nich si Facebook obrázok musí sám
     * stiahnuť a prvé zdieľanie sa zobrazí bez neho.
     */
    'image' => '/images/og-default.jpg',
    'image_width' => 1200,
    'image_height' => 630,

    'locale' => 'sk_SK',

    /*
     * Aplikácia z Facebook Developers. Bez nej sa v štatistikách stránky
     * nepárujú zdieľania s webom.
     */
    'facebook_app_id' => env('SEO_FACEBOOK_APP_ID', '241173683337522'),

    /*
     * Účet na X (Twitteri) v tvare @meno; kým ho web nemá, ostáva prázdny a
     * značka twitter:site sa nevykresľuje.
     */
    'twitter_site' => env('SEO_TWITTER_SITE'),

    /*
     * Profily webu na iných sieťach. Idú do schema.org ako sameAs — Google
     * podľa nich spája web s profilmi do jednej entity.
     */
    'social' => array_values(array_filter([
        env('SEO_FACEBOOK_PAGE'),
        env('SEO_YOUTUBE_CHANNEL'),
    ])),

    /*
     * Farba lišty prehliadača na mobiloch. Drží sa modrej hlavného menu
     * (bg-blue-900) a favicony, nie červeného akcentu verejnej časti — lišta
     * je pokračovaním hlavičky webu, nie textu na stránke.
     */
    'theme_color' => '#1e3a8a',

    /*
     * Routy, ktoré nemajú čo robiť vo vyhľadávaní: administrácia, súkromný
     * profil a prihlasovanie. Značka robots je poistka k robots.txt — ten
     * len prosí, aby sa stránka nesťahovala, noindex ju drží mimo indexu aj
     * keď sa k nej crawler dostane cez odkaz.
     */
    'noindex_routes' => [
        'admin.*',
        'profile.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'favorites.*',
        'userSupport.*',
        // Formuláre na zakladanie a úpravu — nech sú kdekoľvek.
        '*.create',
        '*.edit',
    ],

];
