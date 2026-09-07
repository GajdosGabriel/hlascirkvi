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
        // Komponent comment-item číta comment.user, takže autora načítame
        // v dávke — inak si ho vypýtal každý riadok stránky zvlášť.
        $posts = Comment::with('user')->latest()->filter($filters)->paginate()->withQueryString();
        return view('admins.comments.index', compact('posts'));
    }
}
