<?php

namespace App\Http\Controllers\Admin;

use App\Filters\PostFilters;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Post;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Eloquent\Criteria\LatestFirst;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Models\User;
use Carbon\Carbon;
use CyrildeWit\EloquentViewable\Support\Period;
use DB;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    protected $posts;
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }


    public function home() {

        return view('admins.home');
    }




    public function statistic($days = 1)
    {
//       $posts = Post::where('created_at','>=', Carbon::now()->subDays(6))->orderByViews('desc')->paginate(50);

//       $posts = Post::whereHas('views', function($query) use($days){
//            $query->whereRaw('DATE(viewed_at) > CURDATE() - INTERVAL ' . $days .' DAY');
//        })
//           ->orderBy('views_count', 'desc')
//           ->withCount('views')
////                 ->take(10)
//           ->get();

//        dd($posts);

//
        $posts = DB::table('views')
//           ->take(1000)
              // This month
//            ->whereMonth('viewed_at', date('m'))
            ->whereRaw('DATE(viewed_at) > CURDATE() - INTERVAL ' . $days .' DAY')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->select('viewable_id', DB::raw('count(*) as unique_view , posts.title as title, posts.id as id , posts.count_view as count_view'))
            ->groupBy('viewable_id', 'title', 'id', 'count_view')
            ->orderBy('unique_view', 'desc')
            ->get();
////        dd($posts);
        return view('admins.statistic', ['posts' => $posts]);
    }





}
