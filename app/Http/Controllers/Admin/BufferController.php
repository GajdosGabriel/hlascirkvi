<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Services\Buffer;
use App\Models\Organization;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BufferController extends Controller
{
    protected $posts;
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }

    public function index(Request $request, Buffer $buffer)
    {

        $posts = Post::doesntHave('updaters')->latest();

        if ($request->posts) {
            $posts = $posts->where('organization_id', $request->posts);
        }

        return view(
            'admins.buffer.index',
            [
                'posts' => $posts->paginate(32),
                // Bočný zoznam potrebuje len názov kanála a počet čakajúcich
                // príspevkov. Pôvodné $posts->get()->groupBy() na to načítalo
                // všetky nezverejnené príspevky aj s obrázkami a kanálmi.
                'organizations' => $this->organizationsWithUnpublishedPosts(),
                // Kedy dnes publisher vypustí ďalší príspevok.
                'status' => $buffer->status(),
            ]
        );
    }

    protected function organizationsWithUnpublishedPosts()
    {
        $unpublished = fn ($query) => $query->doesntHave('updaters');

        return Organization::whereHas('posts', $unpublished)
            ->withCount(['posts as unpublished_posts_count' => $unpublished])
            ->orderBy('title')
            ->get();
    }
}
