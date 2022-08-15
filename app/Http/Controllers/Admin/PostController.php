<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    public function __construct()
    {
        $this->post = new EloquentPostRepository;
        $this->middleware('checkSuperAdmin');
    }

    public function index(PostFilters $filters)
    {
        $posts = Post::filter($filters)->paginate(40)->withQueryString();
        
        return view('admins.posts.index', compact('posts'));
    }
}
