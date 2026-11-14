<?php

namespace App\Http\Controllers\Public;

use App\Enums\PostSection;
use App\Http\Controllers\Controller;
use App\Models\Canal;
use App\Models\Post;
use App\Repositories\Contracts\PostRepository;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Atom feed pre čítačky noviniek a agregátory.
 *
 * Úvodná stránka a každý zverejnený kanál majú vlastný feed s posledným
 * výberom príspevkov. Hotové XML sa drží vo vyrovnávacej pamäti ako pri
 * mape webu — čítačky sa pýtajú často a obsah sa mení len s publisherom.
 */
class FeedController extends Controller
{
    /** Koľko posledných príspevkov feed nesie. */
    protected const LIMIT = 30;

    /** Kratšie než pri mape webu: nový príspevok má do čítačky prísť rýchlo. */
    protected const TTL = 30 * 60;

    /** Dĺžka zhrnutia, keď príspevok nemá vlastné `summary`. */
    protected const SUMMARY_LIMIT = 300;

    public function __construct(protected PostRepository $posts)
    {
    }

    /** Príspevky úvodnej stránky. */
    public function index(): Response
    {
        $xml = Cache::remember('feed:front', static::TTL, function () {
            $posts = $this->posts->postsInSection(PostSection::Front)
                // Obľúbené ani obrázky do feedu nejdú, kanál áno (autor).
                ->without(['favorites', 'images'])
                ->latest('published_at')
                ->latest('id')
                ->limit(static::LIMIT)
                ->get();

            return $this->atom(
                id: url('/'),
                title: config('seo.title'),
                subtitle: config('seo.description'),
                self: route('feed'),
                alternate: url('/'),
                posts: $posts,
            );
        });

        return $this->xml($xml);
    }

    /** Príspevky jedného kanála; skrytý kanál feed nemá. */
    public function canal(Canal $canal): Response
    {
        abort_if($canal->published === null, 404);

        $xml = Cache::remember("feed:canal:{$canal->id}", static::TTL, function () use ($canal) {
            $posts = $canal->posts()
                ->published()->available()
                ->without(['favorites', 'images'])
                ->latest('published_at')
                ->latest('id')
                ->limit(static::LIMIT)
                ->get();

            return $this->atom(
                id: route('organizations.show', [$canal->id]),
                title: $canal->title . ' – ' . config('seo.site_name'),
                subtitle: Seo::text(preg_replace('/<[^>]*>/', ' ', (string) $canal->description), static::SUMMARY_LIMIT),
                self: route('organizations.feed', [$canal->id]),
                alternate: route('organizations.show', [$canal->id]),
                posts: $posts,
            );
        });

        return $this->xml($xml);
    }

    /** @param Collection<int, Post> $posts */
    protected function atom(string $id, string $title, ?string $subtitle, string $self, string $alternate, Collection $posts): string
    {
        // Feed bez príspevkov musí mať aj tak <updated>; vtedy poslúži teraz.
        $updated = $posts->map(fn (Post $post) => $this->date($post))->filter()->max()
            ?? Carbon::now()->toAtomString();

        $body = '';

        foreach ($posts as $post) {
            $link = Seo::canonicalUrl(route('post.show', [$post->id, $post->slug]));
            $author = $post->canal?->title ?: config('seo.site_name');
            $summary = Seo::text($post->summary)
                ?: Seo::text(preg_replace('/<[^>]*>/', ' ', (string) $post->body), static::SUMMARY_LIMIT);
            $date = $this->date($post) ?? $updated;

            $body .= "  <entry>\n"
                . '    <id>' . $this->escape($link) . "</id>\n"
                . '    <title>' . $this->escape(Seo::text($post->title) ?: 'Príspevok ' . $post->id) . "</title>\n"
                . '    <link rel="alternate" type="text/html" href="' . $this->escape($link) . "\"/>\n"
                . '    <published>' . $date . "</published>\n"
                . '    <updated>' . $date . "</updated>\n"
                . '    <author><name>' . $this->escape($author) . "</name></author>\n"
                . ($summary !== '' ? '    <summary type="text">' . $this->escape($summary) . "</summary>\n" : '')
                . "  </entry>\n";
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="sk">' . "\n"
            . '  <id>' . $this->escape(Seo::canonicalUrl($id)) . "</id>\n"
            . '  <title>' . $this->escape($title) . "</title>\n"
            . ($subtitle ? '  <subtitle>' . $this->escape($subtitle) . "</subtitle>\n" : '')
            . '  <updated>' . $updated . "</updated>\n"
            . '  <link rel="self" type="application/atom+xml" href="' . $this->escape(Seo::canonicalUrl($self)) . "\"/>\n"
            . '  <link rel="alternate" type="text/html" href="' . $this->escape(Seo::canonicalUrl($alternate)) . "\"/>\n"
            . $body
            . '</feed>';
    }

    /** Dátum položky: zverejnenie z buffera, inak vznik príspevku. */
    protected function date(Post $post): ?string
    {
        $value = $post->published_at ?? $post->created_at;

        return $value ? Carbon::parse($value)->toAtomString() : null;
    }

    protected function escape(string $value): string
    {
        // Riadiace znaky, ktoré XML 1.0 nepovoľuje (chodia v popisoch z
        // YouTube), htmlspecialchars neošetrí — čítačka by celý feed odmietla.
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function xml(string $body): Response
    {
        return response($body, 200, [
            'Content-Type' => 'application/atom+xml; charset=UTF-8',
        ]);
    }
}
