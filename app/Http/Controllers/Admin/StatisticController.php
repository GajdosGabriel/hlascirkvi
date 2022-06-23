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

class StatisticController extends Controller
{
    protected $posts;
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }

    public function index(Request $request)
    {
        $days = $request->lastDays ? $request->lastDays : 1;

        $posts = DB::table('views')
//           ->take(1000)
              // This month
//            ->whereMonth('viewed_at', date('m'))
            ->whereRaw('DATE(viewed_at) > CURDATE() - INTERVAL ' .  $days .' DAY')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->join('organizations', 'organizations.id', '=', 'posts.organization_id')
            ->select('viewable_id', DB::raw('count(*) as unique_view , posts.title as title, posts.id as id, organizations.title as organization , posts.count_view as count_view'))
            ->groupBy('viewable_id', 'title', 'id', 'count_view')
            ->orderBy('unique_view', 'desc')
            ->get();

        return view('admins.statistic', ['posts' => $posts]);
    }





}
