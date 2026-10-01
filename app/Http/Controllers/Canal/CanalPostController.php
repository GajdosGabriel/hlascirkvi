<?php

namespace App\Http\Controllers\Canal;

use App\Models\Post;
use App\Filters\PostFilters;
use App\Models\Canal;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostSaveRequest;
use App\Services\PostService\PostService;

/**
 * Články aktívneho kanála prihláseného užívateľa (/dashboard/posts).
 *
 * Do 10. 9. 2026 bol kanál v adrese (/dashboard/canals/{canal}/posts). Výpis
 * pritom gate-oval len viewAny, takže ktokoľvek prihlásený si podstrčením ID
 * pozrel cudzí archív aj kôš. Kanál sa teraz berie z users.canal_id, rovnako ako
 * na nástenke; úpravy a mazanie jednotlivých článkov naďalej rozhoduje PostPolicy.
 */
class CanalPostController extends Controller
{

    public function __construct(private PostService $postService)
    {
        // `destroy` je z automatickej autorizácie vyňatý zámerne: pracuje aj so
        // zmazanými príspevkami, takže sa {post} nedá naviazať bežnou implicitnou
        // väzbou a middleware `can:delete,post` by dostal reťazec s ID. Kontrola
        // je preto priamo v metóde.
        $this->authorizeResource(Post::class, 'post', ['except' => ['destroy']]);
    }

    public function index(PostFilters $filters)
    {
        if (! $canal = $this->activeCanal()) {
            return $this->withoutCanal();
        }

        // withQueryString(): bez neho odkazy stránkovania zahodili zapnutý
        // filter aj hľadanie a druhá strana sa vrátila k celému výpisu.
        //
        // Počet komentárov potrebuje každý riadok výpisu
        // (profiles/posts/_row); bez neho to bol dopyt na článok, teda
        // tridsať na stranu.
        $posts = $canal->posts()
            ->withCount('comments')
            ->filter($filters)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('profiles.posts.index', compact('posts', 'canal'));
    }

    public function create()
    {
        if (! $canal = $this->activeCanal()) {
            return $this->withoutCanal();
        }

        return view('posts.create', ['post' => new Post, 'canal' => $canal]);
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Post $post, PostSaveRequest $request)
    {
        $this->postService->update($post, $request);

        return redirect()->route('post.show', [$post->id, $post->slug])
            ->with($this->imageWarnings());
    }

    /** Článok sa uložil, no niektorý obrázok nie — používateľ sa to dozvie. */
    private function imageWarnings(): array
    {
        $failures = $this->postService->failures;

        return $failures === [] ? [] : ['flash' => 'Článok bol uložený, ale: ' . implode(' ', $failures)];
    }

    public function store(PostSaveRequest $request)
    {
        if (! $canal = $this->activeCanal()) {
            return $this->withoutCanal();
        }

        $this->postService->store($canal, $request);

        return redirect()->route('profile.posts.index')
            ->with($this->imageWarnings());
    }

    // Zmazať alebo obnoviť Post
    public function destroy($post)
    {
        // $post prichádzalo ako reťazec (bez typového hintu sa implicitná väzba
        // nespustí), takže authorize() dostal ID a PostPolicy sa nenašla —
        // mazanie tak bežným užívateľom vždy odmietlo. A find() mohol vrátiť
        // null, na ktorom potom padlo ->deleted_at.
        $post = Post::withTrashed()->find($post);

        abort_if($post === null, 404);

        $this->authorize('update', $post);

        $isAdmin = auth()->user()->can('admin');

        if ($post->deleted_at) {
            // Článok, ktorý skryl administrátor, správca kanála nevráti.
            abort_if($post->deleted_by_admin && ! $isAdmin, 403, 'Príspevok zmazal administrátor.');

            // Obnovia sa len komentáre zmazané spolu s článkom (rovnaký deleted_at).
            $deletedAt = $post->deleted_at;
            $post->restore();
            $post->comments()->onlyTrashed()->where('deleted_at', $deletedAt)->restore();
            $post->forceFill(['deleted_by_admin' => false])->saveQuietly();

            return redirect()->route('profile.posts.index')->with('flash', 'Príspevok bol obnovený!');
        }

        $post->delete();
        // Komentáre dostanú rovnaký čas zmazania ako článok; už zmazané ostanú nedotknuté.
        $post->comments()->update(['deleted_at' => $post->deleted_at]);
        $post->forceFill(['deleted_by_admin' => $isAdmin])->saveQuietly();

        return redirect()->route('profile.posts.index')->with('flash', 'Príspevok bol zmazaný!');
    }

    protected function activeCanal(): ?Canal
    {
        return auth()->user()->canal;
    }

    // Užívateľ bez aktívneho kanála nemá čo vypísať ani kam zapisovať.
    protected function withoutCanal()
    {
        return redirect()->route('profile.canals.index')
            ->with('flash', 'Najprv si vyberte kanál, s ktorým chcete pracovať.');
    }
}
