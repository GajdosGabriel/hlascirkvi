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

    public function youtubeBlocked(Request $request)
    {
        Post::withoutGlobalScope('published')->whereId($request->post)->first()->update(['youtube_blocked' => 1]);

        return redirect()->route('admin.unpublished')
            ->with(session()->flash('flash', 'Vídeo je blokované!'));
    }

    // Ručné zverejnenie videa
    public function bufferedVideosPublish(Request $request)
    {
        $this->posts->findAndPublishPost($request->post, $idUpdater = 15);

        session()->flash('flash', 'Príspevok zverejnený!');
        return back();
    }

}
