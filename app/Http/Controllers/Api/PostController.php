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

    public function update($post, Request $request)
    {

        if ($request->idUpdater) {
            // idUpdater = 15
            $this->post->findAndPublishPost($post, $request->idUpdater);
            return;
        }

        Post::whereId($post)->first()->update($request->all());
    }
}
