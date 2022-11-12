<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Filters\CommentFilters;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }


    public function index(CommentFilters $filters){
        $posts = Comment::latest()->filter($filters)->paginate()->withQueryString();
        return view('admins.comments.index', compact('posts'));
    }
}
