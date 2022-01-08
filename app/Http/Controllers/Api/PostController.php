<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostCollection;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    public function __construct()
    {
        $this->post = new EloquentPostRepository;
    }

    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(28);
        return new PostCollection($posts);
    }

    public function update($post, Request $request){

        if ($request->idUpdater) {
            // idUpdater = 15
            $this->post->findAndPublishPost($post, $request->idUpdater);
            return;
        }

        Post::whereId($post)->first()->update( $request->all() );

    }
}
