<?php

namespace App\Http\Controllers\Public;


use Youtube;
use App\Enums\PostSection;
use App\Models\Post;
use App\Filters\PostFilters;
use App\Services\CreditUser;
use Illuminate\Http\Request;
use App\Services\VisitModels\ViewRecorder;
use App\Repositories\Contracts\PostRepository;
use App\Http\Controllers\Controller;






class PostController extends Controller
{
    protected PostRepository $post;

    public function __construct(PostRepository $postRepository)
    {
        $this->post = $postRepository;
    }



    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsInSection(PostSection::Front)->filter($filters)->paginate(30);

        return view('posts.index', compact('posts'));
    }



    public function show(Post $post, CreditUser $creditUser, ?string $slug = null)
    {
        // Vypnutý kanál odfiltruje middleware `bannedCanal` (routes/web.php).

        // Kanonická adresa je /post/{id}/{slug}. Chýbajúci alebo starý slug
        // (po premenovaní videa) presmeruje natrvalo — inak by každý variant
        // vrátil 200 a Search Console ho viedla ako alternatívnu stránku.
        if ($post->slug !== null && $post->slug !== '' && $slug !== $post->slug) {
            $query = request()->getQueryString();

            return redirect(route('post.show', [$post->id, $post->slug]) . ($query ? '?' . $query : ''), 301);
        }

        $creditUser->setPostHistory($post);

        // Zobrazenie sa tu nezapisuje — pošle ho až prehliadač cez view()
        // (resources/js/article.js). Crawlery bez JS a session sa tak do
        // počítadla nedostanú.

        return view('posts.show', [
            'post'    => $post,
            'series'  => $this->series($post),
            'isSaved' => (bool) auth()->user()?->savedPosts()->whereKey($post->id)->exists(),
        ] + $this->channelPanels($post));
    }


    /**
     * Beacon zobrazenia z detailu príspevku. Odpoveď je vždy rovnaká, aby
     * nebolo zvonku vidno, či sa zobrazenie započítalo.
     */
    public function view(Post $post, Request $request, ViewRecorder $recorder)
    {
        $recorder->record($post, $request);

        return response()->noContent();
    }


    /**
     * Seminár (séria prednášok), do ktorého príspevok patrí, s dielmi v poradí.
     * Pivot poradie nemá; import z playlistu zakladá príspevky v poradí
     * playlistu, takže ho drží ID. Diely potrebujú len titulok a odkaz,
     * preto bez väzieb z $with.
     */
    protected function series(Post $post): ?array
    {
        $seminar = $post->seminars()->latest('seminars.id')->first();

        if (! $seminar) {
            return null;
        }

        $parts = $seminar->posts()
            ->without(['favorites', 'images', 'canal'])
            ->published()->available()
            ->orderBy('posts.id')
            ->get(['posts.id', 'posts.title', 'posts.slug', 'posts.video_duration']);

        $index = $parts->search(fn ($part) => $part->id === $post->id);

        // Jediný diel (alebo nezverejnený príspevok) sériu netvorí.
        if ($index === false || $parts->count() < 2) {
            return null;
        }

        return [
            'seminar'  => $seminar,
            'parts'    => $parts,
            'index'    => $index,
            'previous' => $parts->get($index - 1),
            'next'     => $parts->get($index + 1),
        ];
    }


    /**
     * Ďalšia dávka archívu kanála pre vodorovný pás. Vracia hotové karty,
     * nie dáta — pás tak vyzerá rovnako, nech ho vykreslí Blade pri načítaní
     * stránky alebo fetch pri posune vpravo, a karta má jednu predlohu.
     */
    public function rail(Post $post)
    {
        $rail = $this->post->canalRail($post->canal_id, $post->id);

        return response()->json([
            'html' => view('posts._rail-items', ['items' => $rail])->render(),
            // Prázdny kurzor je pre prehliadač znamenie, že archív skončil.
            'next' => optional($rail->nextCursor())->encode(),
        ]);
    }


    /**
     * Panely okolo článku: pás archívu pod ním a výbery z kanála v bočnom
     * paneli. Sú to krátke dopyty na indexe organizácie, preto stoja pri
     * sebe — pohľad ich len vykreslí.
     */
    protected function channelPanels(Post $post)
    {
        $rail  = $this->post->canalRail($post->canal_id, $post->id);
        $first = $this->post->firstInCanal($post->canal_id, $post->id);

        // "Pred rokom" má zmysel len v kanáli, ktorý rok prežil; inak by
        // ukazoval ten istý príspevok ako "Ako to začalo".
        $yearAgo = null;
        $moment  = now()->subYear();

        if ($first && $first->created_at->lt($moment)) {
            $yearAgo = $this->post->inCanalBefore($post->canal_id, $post->id, $moment);
        }

        return [
            'rail'      => $rail,
            'railTotal' => $this->post->countInCanal($post->canal_id),
            'topPosts'  => $this->post->mostViewedInCanal($post->canal_id, $post->id),
            'firstPost' => $first,
            'yearAgo'   => $yearAgo && $first && $yearAgo->isNot($first) ? $yearAgo : null,
        ];
    }
}
