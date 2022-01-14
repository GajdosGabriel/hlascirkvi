<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    public function index(){
        $posts = Comment::latest()->paginate();
        return view('admins.comments.index', compact('posts'));
    }
}
