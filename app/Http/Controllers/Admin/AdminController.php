<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepository;
use App\Services\Dashboard\AdminDashboardStats;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $posts;
    
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }


    public function index(Request $request, AdminDashboardStats $stats)
    {
        return view('admins.home', $stats->cached($request->boolean('refresh')));
    }
}
