<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepository;
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
        // Strop je tu preto, že rozsah ide priamo do dopytu — a zároveň
        // tabuľka `views` siaha len 90 dní dozadu (app:views-prune).
        $days = min(max((int) $request->lastDays, 1), 90);

        // `unique_view` je počet návštevníkov za obdobie — v tabuľke `views` je
        // od každého najviac jeden riadok na deň. `count_view` vedľa neho je
        // trvalý súčet zo samotného príspevku.
        $query = DB::table('views')
            ->where('views.viewable_type', Post::class)
            ->where('views.viewed_on', '>=', Carbon::today()->subDays($days)->toDateString())
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->join('canals', 'canals.id', '=', 'posts.canal_id')
            ->select('views.viewable_id', DB::raw('count(*) as unique_view'), 'posts.title as title', 'posts.id as id', 'posts.slug as slug', 'canals.title as canal', 'posts.count_view as count_view')
            ->groupBy('views.viewable_id', 'posts.title', 'posts.id', 'posts.slug', 'canals.title', 'posts.count_view')
            ->orderBy('unique_view', 'desc')
            ->orderBy('posts.id', 'desc');

        // Súhrnné metriky počítame z celého výberu, nie len z aktuálnej strany.
        $totals = DB::query()
            ->fromSub((clone $query)->reorder(), 'stats')
            ->selectRaw('count(*) as posts, coalesce(sum(unique_view), 0) as unique_views')
            ->first();

        $topPost = (clone $query)->first();

        $posts = $query->paginate(50)->withQueryString();

        return view('admins.statistic', [
            'posts' => $posts,
            'days' => $days,
            'totalPosts' => (int) $totals->posts,
            'totalUniqueViews' => (int) $totals->unique_views,
            'topPost' => $topPost,
        ]);
    }





}
