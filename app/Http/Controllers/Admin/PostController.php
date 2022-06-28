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
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(28);
        
        return view('admins.posts.index', compact('posts'));
    }
}
