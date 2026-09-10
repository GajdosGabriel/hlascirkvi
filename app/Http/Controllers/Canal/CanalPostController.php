<?php

namespace App\Http\Controllers\Canal;

use App\Models\Post;
use App\Filters\PostFilters;
use App\Models\Canal;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostSaveRequest;
use App\Services\PostService\PostService;

class CanalPostController extends Controller
{

    public function __construct(private PostService $postService)
    {
        // `destroy` je z automatickej autorizácie vyňatý zámerne: pracuje aj so
        // zmazanými príspevkami, takže sa {post} nedá naviazať bežnou implicitnou
        // väzbou a middleware `can:delete,post` by dostal reťazec s ID. Kontrola
        // je preto priamo v metóde.
        $this->authorizeResource(Post::class, 'post', ['except' => ['destroy']]);
        $this->authorizeResource(Canal::class, 'canal');
    }

    public function index(Canal $canal, PostFilters $filters)
    {
        // withQueryString(): bez neho odkazy stránkovania zahodili zapnutý
        // filter aj hľadanie a druhá strana sa vrátila k celému výpisu.
        //
        // updaters aj počet komentárov potrebuje každý riadok výpisu
        // (profiles/posts/_row); bez nich to boli dva dopyty na článok, teda
        // šesťdesiat na stranu.
        $posts = $canal->posts()
            ->with('updaters')
            ->withCount('comments')
            ->filter($filters)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('profiles.posts.index', compact('posts', 'canal'));
    }

    public function create(Canal $canal)
    {
        return view('posts.create', ['post' => new Post, 'canal' => $canal]);
    }

    public function edit(Canal $canal, Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', compact('post', 'canal'));
    }

    public function update(Canal $canal, Post $post,  PostSaveRequest $request)
    {
        $this->postService->update($post, $request);

        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function store(Canal $canal, PostSaveRequest $request)
    {
        $this->postService->store($canal, $request);

        return redirect()->route('profile.canals.posts.index', [$canal->id]);
    }

    // Zmazať alebo obnoviť Post
    public function destroy(Canal $canal, $post)
    {
        // $post prichádzalo ako reťazec (bez typového hintu sa implicitná väzba
        // nespustí), takže authorize() dostal ID a PostPolicy sa nenašla —
        // mazanie tak bežným užívateľom vždy odmietlo. A find() mohol vrátiť
        // null, na ktorom potom padlo ->deleted_at.
        $post = Post::withTrashed()->find($post);

        abort_if($post === null, 404);

        $this->authorize('update', $post);

        if ($post->deleted_at) {
            $post->restore();
            $post->comments()->restore();
            return redirect()->route('profile.canals.posts.index', $canal->id)->with(session()->flash('flash', 'Príspevok bol obnovený!'));
        } else {
            $post->comments()->delete();
            $post->delete();
        }

        return redirect()->route('profile.canals.posts.index', $canal->id)->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
