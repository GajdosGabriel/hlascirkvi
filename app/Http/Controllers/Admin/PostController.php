<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    protected $post;
    
    public function __construct()
    {
        $this->post = new EloquentPostRepository;
        $this->middleware('checkSuperAdmin');
    }

    public function index(PostFilters $filters)
    {
        // Karta príspevku vypisuje počet komentárov
        // (posts/card-admin.blade.php:55). Berie sa cez withCount, lebo
        // comments()->count() je dopyt na relation builderi — vykonal by sa
        // aj pri načítanej väzbe.
        $posts = Post::query()
            ->withCount('comments')
            ->filter($filters)
            ->paginate(40)
            ->withQueryString();

        return view('admins.posts.index', compact('posts'));
    }
}
