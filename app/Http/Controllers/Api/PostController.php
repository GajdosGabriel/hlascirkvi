<?php

namespace App\Http\Controllers\Api;

use App\Filters\PostFilters;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostCollection;
use App\Repositories\Eloquent\EloquentPostRepository;
use Illuminate\Http\Request;

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
}
