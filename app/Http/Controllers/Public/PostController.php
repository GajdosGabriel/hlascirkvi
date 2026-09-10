<?php

namespace App\Http\Controllers\Public;


use Youtube;
use App\Models\Post;
use App\Filters\PostFilters;
use App\Services\CreditUser;
use Illuminate\Http\Request;
use App\Events\VisitModel;
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
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(30);

        return view('posts.index', compact('posts'));
    }



    public function show(Post $post, $slug, CreditUser $creditUser)
    {
        // Vypnutý kanál odfiltruje middleware `bannedCanal` (routes/web.php).

        // Šablóna serializuje $post do Vue komponentov, čo zakaždým vyhodnotí
        // hasUpdater. S načítanou väzbou sa atribút prečíta z pamäte.
        $post->load('updaters');

        $creditUser->setPostHistory($post);

        event(new VisitModel($post));

        return view('posts.show', ['post' => $post] + $this->channelPanels($post));
    }


    /**
     * Ďalšia dávka archívu kanála pre vodorovný pás. Vracia hotové karty,
     * nie dáta — pás tak vyzerá rovnako, nech ho vykreslí Blade pri načítaní
     * stránky alebo fetch pri posune vpravo, a karta má jednu predlohu.
     */
    public function rail(Post $post)
    {
        $rail = $this->post->organizationRail($post->organization_id, $post->id);

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
        $rail  = $this->post->organizationRail($post->organization_id, $post->id);
        $first = $this->post->firstInOrganization($post->organization_id, $post->id);

        // "Pred rokom" má zmysel len v kanáli, ktorý rok prežil; inak by
        // ukazoval ten istý príspevok ako "Ako to začalo".
        $yearAgo = null;
        $moment  = now()->subYear();

        if ($first && $first->created_at->lt($moment)) {
            $yearAgo = $this->post->inOrganizationBefore($post->organization_id, $post->id, $moment);
        }

        return [
            'rail'      => $rail,
            'railTotal' => $this->post->countInOrganization($post->organization_id),
            'topPosts'  => $this->post->mostViewedInOrganization($post->organization_id, $post->id),
            'firstPost' => $first,
            'yearAgo'   => $yearAgo && $first && $yearAgo->isNot($first) ? $yearAgo : null,
        ];
    }
}
