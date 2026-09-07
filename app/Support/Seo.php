<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

/**
 * Jedno miesto, kde sa skladajú značky pre vyhľadávače a náhľady na sieťach.
 *
 * Šablóna nastaví pole $seo s tým, čo o stránke vie (titulok, popis, obrázok),
 * a partials/meta ho cez resolve() doplní o predvolené hodnoty webu a vykreslí.
 * Predtým si každá šablóna písala vlastnú sadu og: značiek — polovica stránok
 * ich nemala vôbec a tie zvyšné mali každá iný rozsah.
 */
class Seo
{
    /** Meta description znesie v Google zhruba toľko znakov. */
    protected const DESCRIPTION_LIMIT = 160;

    /** Náhľad na Facebooku je širší, popis v ňom môže byť dlhší. */
    protected const OG_DESCRIPTION_LIMIT = 200;

    /**
     * Doplní zadané kľúče o predvolené hodnoty webu a vráti hotové hodnoty
     * pre šablónu — už orezané, s absolútnymi adresami a bez HTML.
     */
    public static function resolve(array $seo = []): array
    {
        $siteName = config('seo.site_name');

        $title = static::text($seo['title'] ?? null) ?: config('seo.title');

        // Popis prichádza aj ako celé telo článku; orezanie na dve rôzne dĺžky
        // je preto tu a nie v každej šablóne zvlášť.
        $description = static::text($seo['description'] ?? null) ?: config('seo.description');

        $image = static::url($seo['image'] ?? null);

        // Rozmery patria k obrázku: pri cudzom obrázku ich nepoznáme, pri
        // predvolenom áno. Uviesť rozmery predvoleného obrázka pri cudzom by
        // Facebooku podsunulo nesprávny pomer strán.
        if ($image === null) {
            $image = static::url(config('seo.image'));
            $width = config('seo.image_width');
            $height = config('seo.image_height');
        } else {
            $width = $seo['image_width'] ?? null;
            $height = $seo['image_height'] ?? null;
        }

        return [
            // Titulok v záložke nesie aj meno webu, og:title už nie — v náhľade
            // odkazu meno webu stojí na vlastnom riadku a opakovalo by sa.
            'document_title' => static::documentTitle($title, $siteName),
            'title' => $title,
            'site_name' => $siteName,

            'description' => Str::limit($description, static::DESCRIPTION_LIMIT),
            'og_description' => Str::limit($description, static::OG_DESCRIPTION_LIMIT),

            'canonical' => static::canonicalUrl($seo['canonical'] ?? null) ?: static::canonicalUrl(url()->current()),
            'prev' => static::canonicalUrl($seo['prev'] ?? null),
            'next' => static::canonicalUrl($seo['next'] ?? null),
            'robots' => static::robots($seo['noindex'] ?? null),

            'type' => $seo['type'] ?? 'website',
            'locale' => config('seo.locale'),

            'image' => $image,
            'image_alt' => static::text($seo['image_alt'] ?? null) ?: $title,
            'image_width' => $width,
            'image_height' => $height,
            'image_type' => static::mimeType($image),

            // Značky článku (article:*) číta Facebook; Google si to isté berie
            // zo schema.org nižšie.
            'published' => static::date($seo['published'] ?? null),
            'modified' => static::date($seo['modified'] ?? null),
            'author' => static::text($seo['author'] ?? null),
            'section' => static::text($seo['section'] ?? null),
            'tags' => array_filter(array_map(
                fn ($tag) => static::text($tag),
                (array) ($seo['tags'] ?? [])
            )),

            'video' => $seo['video'] ?? null,

            'twitter_card' => $seo['twitter_card'] ?? 'summary_large_image',
            'twitter_site' => config('seo.twitter_site'),
            'facebook_app_id' => config('seo.facebook_app_id'),

            // Štruktúrované dáta. Šablóna ich pridáva ako hotové polia, aby sa
            // kľúče typu "@context" nedostali do Blade ako direktívy.
            'jsonld' => array_values(array_filter($seo['jsonld'] ?? [])),
        ];
    }

    /**
     * Text z databázy do atribútu content: bez značiek, bez entít a na jednom
     * riadku. Popisy z YouTube chodia s odsekmi aj s HTML a bez tohto by sa
     * do meta značky dostali aj so značkami.
     */
    public static function text($value, ?int $limit = null): string
    {
        if ($value === null) {
            return '';
        }

        $text = strip_tags((string) $value);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return $limit ? Str::limit($text, $limit) : $text;
    }

    /**
     * Absolútna adresa. Facebook ani Google relatívnu cestu v og:image
     * a canonical neprijmú.
     */
    public static function url($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        if ($base = config('seo.url')) {
            return rtrim($base, '/') . '/' . ltrim($value, '/');
        }

        return url($value);
    }

    /**
     * Adresa prepísaná na kanonickú doménu z config/seo.php.
     *
     * Web je dostupný na viacerých adresách (s www aj bez, http aj https)
     * a url()->current() vracia tú, cez ktorú návštevník práve prišiel.
     * Bez prepisu by canonical, og:url aj mapa webu rozdelili jednu stránku
     * na niekoľko adries a s nimi aj jej pozíciu vo vyhľadávaní.
     *
     * Netýka sa obrázkov — tie ležia na vlastnom úložisku a adresu si nesú
     * celú vlastnú.
     */
    public static function canonicalUrl($value): ?string
    {
        $value = static::url($value);

        if ($value === null || ! ($base = config('seo.url'))) {
            return $value;
        }

        $parts = parse_url($value);
        $baseParts = parse_url($base);

        if (empty($baseParts['host'])) {
            return $value;
        }

        return ($baseParts['scheme'] ?? 'https') . '://'
            . $baseParts['host']
            . (isset($baseParts['port']) ? ':' . $baseParts['port'] : '')
            . ($parts['path'] ?? '/')
            . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    /** Schema.org WebSite — meno webu a vyhľadávanie v ňom. */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => static::url('/'),
            'inLanguage' => 'sk-SK',
            // Google z toho vie priamo vo výsledku ponúknuť pole na hľadanie.
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => static::url('/') . '?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /** Schema.org Organization — vydavateľ obsahu, teda samotný web. */
    public static function publisher(): array
    {
        $publisher = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('seo.site_name'),
            'url' => static::url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => static::url(config('seo.image')),
                'width' => config('seo.image_width'),
                'height' => config('seo.image_height'),
            ],
        ];

        if ($social = config('seo.social')) {
            $publisher['sameAs'] = array_values($social);
        }

        return $publisher;
    }

    /**
     * Drobčeky pre výsledok vyhľadávania. Položky sú dvojice [názov, adresa];
     * posledná (aktuálna stránka) adresu mať nemusí.
     */
    public static function breadcrumbs(array $items): array
    {
        $elements = [];
        $position = 1;

        foreach ($items as [$name, $url]) {
            $element = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => static::text($name, 100),
            ];

            if ($url) {
                $element['item'] = static::url($url);
            }

            $elements[] = $element;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /** Titulok stránky s menom webu — ak ho už sám nenesie. */
    protected static function documentTitle(string $title, string $siteName): string
    {
        return Str::contains($title, $siteName)
            ? $title
            : $title . ' | ' . $siteName;
    }

    /**
     * Administrácia a prihlasovanie do indexu nepatria. Šablóna to môže
     * prebiť kľúčom noindex, inak rozhoduje meno routy.
     */
    protected static function robots(?bool $noindex): string
    {
        if ($noindex === null) {
            $name = Route::currentRouteName();

            $noindex = $name !== null
                && Str::is(config('seo.noindex_routes', []), $name);
        }

        return $noindex
            ? 'noindex, nofollow'
            // max-image-preview:large je podmienka pre veľký náhľad obrázka
            // vo výsledkoch Google Discover.
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }

    /** Dátum v ISO 8601 — jediný tvar, ktorý article:* a schema.org čítajú. */
    protected static function date($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toAtomString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Typ obrázka podľa prípony; Facebook ho pri prvom zdieľaní ocení. */
    protected static function mimeType(?string $url): ?string
    {
        $extension = strtolower(pathinfo(parse_url((string) $url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => null,
        };
    }
}
