<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\EloquentPostRepository;

class PostController extends Controller
{
    public function __construct(EloquentPostRepository $eloquentPostRepository)
    {
        $this->posts = $eloquentPostRepository;
        $this->middleware('checkSuperAdmin');
    }
    
    public function index(PostFilters $filters)
    {
        $posts = $this->posts->postsByUpdater(15)->latest()->filter($filters)->paginate(28);

        return view('admins.posts.index', compact('posts'));
    }

}
