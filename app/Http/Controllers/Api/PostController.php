<?php

namespace App\Http\Controllers\Api;

use App\Enums\PostSection;
use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    protected PostRepository $post;

    public function __construct(PostRepository $postRepository)
    {
        $this->post = $postRepository;
    }

    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsInSection(PostSection::Front)
            ->filter($filters)
            ->paginate(28);

        return PostResource::collection($posts);
    }

    /**
     * Zverejnenie príspevku z Bufferu a zablokovanie YouTube videa.
     * Iné pole sem neprichádza — tlačidlá sú v resources/js/posts/card/buttons.vue
     * a vykresľujú sa len na admin.buffer.index.
     *
     * Pôvodne tu bolo `Post::whereId($post)->first()->update($request->all())`
     * na route mimo auth, takže ktokoľvek vedel prepísať ľubovoľný príspevok.
     *
     * Do 9/2026 sa posielalo `idUpdater` — id z číselníka, ktoré zároveň
     * znamenalo „zverejni". Príspevok si zaradenie nesie sám od importu,
     * takže tlačidlo posiela už len to, čo naozaj robí.
     */
    public function update(Post $post, Request $request)
    {
        $data = $request->validate([
            'publish'         => 'nullable|boolean',
            'youtube_blocked' => 'nullable|boolean',
        ]);

        if (! empty($data['publish'])) {
            $this->post->findAndPublishPost($post->id);
            return;
        }

        if (array_key_exists('youtube_blocked', $data)) {
            $post->update(['youtube_blocked' => $data['youtube_blocked']]);
        }
    }
}
