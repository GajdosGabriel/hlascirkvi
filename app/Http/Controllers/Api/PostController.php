<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    public function __construct(PostRepository $postRepository)
    {
        $this->post = $postRepository;
    }

    public function index(PostFilters $filters)
    {
        // PostResource číta hasUpdater a OrganizationResource vypisuje updaterov
        // kanála — obe väzby preto naťaháme v dávke, nie riadok po riadku.
        $posts = $this->post->postsByUpdater(15)
            ->with(['updaters', 'organization.updaters'])
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
     */
    public function update(Post $post, Request $request)
    {
        $data = $request->validate([
            'idUpdater'       => 'nullable|integer|exists:updaters,id',
            'youtube_blocked' => 'nullable|boolean',
        ]);

        if (! empty($data['idUpdater'])) {
            $this->post->findAndPublishPost($post->id, $data['idUpdater']);
            return;
        }

        if (array_key_exists('youtube_blocked', $data)) {
            $post->update(['youtube_blocked' => $data['youtube_blocked']]);
        }
    }
}
