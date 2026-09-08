<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Seminar;
use App\Models\Verse;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Mapa webu pre vyhľadávače.
 *
 * Príspevkov je vyše štyridsaťtisíc, čo je nad odporúčaným stropom jedného
 * súboru, preto /sitemap.xml je len rozcestník a samotné adresy sú rozdelené
 * po dávkach. Každý súbor sa počíta raz za pár hodín a potom sa vydáva
 * z vyrovnávacej pamäte — inak by každý prechod crawlera znamenal prejsť
 * celú tabuľku príspevkov.
 */
class SitemapController extends Controller
{
    /** Sitemaps.org povoľuje 50 000 adries na súbor; nižšia dávka drží súbor
     *  v rozumnej veľkosti aj pri pomalom pripojení. */
    protected const PER_FILE = 5000;

    protected const TTL = 6 * 3600;

    /** Rozcestník: odkazuje na jednotlivé mapy. */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index', static::TTL, function () {
            $sitemaps = [
                route('sitemap.pages'),
                route('sitemap.organizations'),
            ];

            $pages = (int) ceil($this->postsQuery()->count() / static::PER_FILE);

            for ($page = 1; $page <= max($pages, 1); $page++) {
                $sitemaps[] = route('sitemap.posts', [$page]);
            }

            $body = '';
            $lastmod = $this->iso(Post::max('updated_at'));

            foreach ($sitemaps as $loc) {
                $body .= '  <sitemap><loc>' . $this->escape($loc) . '</loc>'
                    . ($lastmod ? '<lastmod>' . $lastmod . '</lastmod>' : '')
                    . "</sitemap>\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
                . $body
                . '</sitemapindex>';
        });

        return $this->xml($xml);
    }

    /** Stále stránky, semináre a denné zamyslenia. */
    public function pages(): Response
    {
        $xml = Cache::remember('sitemap:pages', static::TTL, function () {
            $urls = [
                ['loc' => url('/'), 'changefreq' => 'hourly', 'priority' => '1.0'],
                ['loc' => route('online-prenosy'), 'changefreq' => 'weekly', 'priority' => '0.8'],
                ['loc' => route('konferencie.pute'), 'changefreq' => 'weekly', 'priority' => '0.7'],
                ['loc' => route('akcie.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
                ['loc' => route('modlitby.index'), 'changefreq' => 'daily', 'priority' => '0.7'],
                ['loc' => route('verses.index'), 'changefreq' => 'daily', 'priority' => '0.7'],
                ['loc' => route('gdpr'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ];

            foreach (Seminar::whereNotNull('published')->get(['id', 'updated_at']) as $seminar) {
                $urls[] = [
                    'loc' => route('seminars.show', [$seminar->id]),
                    'lastmod' => $this->iso($seminar->updated_at),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            }

            // Zamyslenie na každý deň v roku má vlastnú adresu a mení sa len
            // výnimočne — pre vyhľadávač je to stabilný obsah.
            foreach (Verse::orderBy('id')->pluck('slug') as $slug) {
                $urls[] = [
                    'loc' => route('verses.index', [$slug]),
                    'changefreq' => 'yearly',
                    'priority' => '0.4',
                ];
            }

            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** Profily kanálov. */
    public function organizations(): Response
    {
        $xml = Cache::remember('sitemap:organizations', static::TTL, function () {
            $urls = Organization::where('published', 1)
                ->orderBy('id')
                ->get(['id', 'updated_at'])
                ->map(fn ($organization) => [
                    'loc' => route('organizations.show', [$organization->id]),
                    'lastmod' => $this->iso($organization->updated_at),
                    'changefreq' => 'daily',
                    'priority' => '0.7',
                ])
                ->all();

            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** Jedna dávka príspevkov. */
    public function posts(int $page): Response
    {
        abort_if($page < 1, 404);

        $xml = Cache::remember("sitemap:posts:{$page}", static::TTL, function () use ($page) {
            $posts = $this->postsQuery()
                // Post má v $with obľúbené, obrázky aj kanál. Do mapy webu ide
                // len adresa a dátum, tak nech sa načítava len to.
                ->without(['favorites', 'images', 'organization'])
                ->orderBy('id')
                ->forPage($page, static::PER_FILE)
                ->get(['id', 'slug', 'updated_at']);

            abort_if($posts->isEmpty() && $page > 1, 404);

            $urls = $posts->map(fn ($post) => [
                'loc' => route('post.show', [$post->id, $post->slug]),
                'lastmod' => $this->iso($post->updated_at),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ])->all();

            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /**
     * Do mapy patrí to isté, čo je verejne dostupné: príspevok vypnutého
     * kanála detail odmietne (405) a video, ktoré na YouTube zmizlo, nemá
     * návštevníkovi čo ponúknuť.
     */
    protected function postsQuery()
    {
        return Post::query()
            // A post without a slug has no valid detail URL. Filter before pagination.
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->whereNull('video_available')
            ->whereHas('organization', fn ($query) => $query->where('published', 1));
    }

    protected function urlset(array $urls): string
    {
        $body = '';

        foreach ($urls as $url) {
            $body .= '  <url><loc>' . $this->escape($url['loc']) . '</loc>'
                . (empty($url['lastmod']) ? '' : '<lastmod>' . $url['lastmod'] . '</lastmod>')
                . (empty($url['changefreq']) ? '' : '<changefreq>' . $url['changefreq'] . '</changefreq>')
                . (empty($url['priority']) ? '' : '<priority>' . $url['priority'] . '</priority>')
                . "</url>\n";
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . $body
            . '</urlset>';
    }

    protected function xml(string $body): Response
    {
        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Adresa do mapy webu. Prepisuje sa na kanonickú doménu — mapa sa dá
     * stiahnuť cez ktorúkoľvek adresu webu a route() by do nej vpísal tú,
     * cez ktorú prišiel crawler. Slugy nesú diakritiku, preto ešte ošetrenie
     * pre XML.
     */
    protected function escape(string $value): string
    {
        return htmlspecialchars(Seo::canonicalUrl($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function iso($value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->toAtomString() : null;
    }
}
