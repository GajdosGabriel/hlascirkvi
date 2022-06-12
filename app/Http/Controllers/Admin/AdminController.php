<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepository;
use CyrildeWit\EloquentViewable\Support\Period;
use App\Repositories\Eloquent\Criteria\LatestFirst;
use App\Repositories\Eloquent\EloquentPostRepository;

class AdminController extends Controller
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
