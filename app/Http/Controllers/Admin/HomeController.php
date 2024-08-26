<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepository;

class HomeController extends Controller
{
    protected $posts;
    
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }


    public function index()
    {
        return view('admins.home');
    }
}
