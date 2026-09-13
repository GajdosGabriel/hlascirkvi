<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepository;
use App\Services\Dashboard\AdminDashboardStats;

class AdminController extends Controller
{
    protected $posts;
    
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }


    public function index(AdminDashboardStats $stats)
    {
        return view('admins.home', $stats->get());
    }
}
