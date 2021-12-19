<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
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

        $posts = Post::withoutGlobalScope('published')->doesntHave('updaters')->latest();

        if ($request->posts) {
            $posts = $posts->where('organization_id', $request->posts);
        }

        return view(
            'admins.buffer.index',
            [
                'posts' => $posts->paginate(32),
                'users' => $posts->get()->groupBy('organization_id')
            ]
        );
    }
}
