<?php

namespace App\Http\Controllers\Admin;

use App\Post;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuffersController extends Controller
{
    protected $posts;
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }

    public function indexBufferedVideos(Request $request)
    {
        // For zoznam nepublikovaných users.
        $users = $this->posts->getUnpublishedPosts()->groupBy('organization_id');

        $posts = Post::withoutGlobalScope('published')->doesntHave('updaters')->latest();

        if($request->posts) {
            $posts = $posts->where('organization_id', $request->posts);
        }

        $posts = $posts->paginate(32);

        return view('admins.buffer.index', ['posts' => $posts, 'users' => $users]);
    }


}
