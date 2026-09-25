<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Filters\CommentFilters;
use App\Http\Controllers\Controller;
use App\Services\Youtube\CommentSync;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }


    public function index(CommentFilters $filters)
    {
        $source = request('source', 'users');
        abort_unless(in_array($source, ['users', 'youtube', 'all'], true), 422);
        $comments = Comment::with(['user:id,first_name,last_name,avatar', 'parent:id,body,user_name'])
            ->when($source !== 'all', fn ($query) => $this->source($query, $source))
            ->withCount('replies')
            ->filter($filters)
            ->paginate()
            ->withQueryString();

        return view('admins.comments.index', [
            'comments' => $comments,
            'posts' => $this->posts($comments->getCollection()),
            'authorCounts' => $this->authorCounts($comments->getCollection()),
            'summary' => $this->summary(),
            'hotPosts' => $this->hotPosts($source),
            'source' => $source,
        ]);
    }

    /**
     * Článok, pod ktorým komentár visí, aj s kanálom a počtom komentárov.
     * Holé riadky zámerne — cez morphTo by si každý komentár dotiahol celý
     * Post aj s jeho $with (obrázky, kanál…).
     */
    private function posts($comments)
    {
        $ids = $comments->where('commentable_type', Post::class)->pluck('commentable_id')->unique();

        if ($ids->isEmpty()) {
            return collect();
        }

        $counts = DB::table('comments')
            ->where('commentable_type', Post::class)
            ->whereIn('commentable_id', $ids)
            ->whereNull('deleted_at')
            ->groupBy('commentable_id')
            ->pluck(DB::raw('count(*)'), 'commentable_id');

        return DB::table('posts')
            ->leftJoin('canals', 'canals.id', '=', 'posts.canal_id')
            ->whereIn('posts.id', $ids)
            ->get([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.video_id',
                'posts.count_view',
                'posts.deleted_at',
                'canals.id as canal_id',
                'canals.title as canal',
            ])
            ->each(fn ($post) => $post->comments = (int) ($counts[$post->id] ?? 0))
            ->keyBy('id');
    }

    /**
     * Koľko komentárov napísal autor celkovo. Komentáre z YouTube visia na
     * jednom technickom účte, pri nich by číslo nič nehovorilo.
     */
    private function authorCounts($comments)
    {
        $ids = $comments->pluck('user_id')
            ->filter(fn ($id) => $id && (int) $id !== CommentSync::USER_ID)
            ->unique();

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('comments')
            ->whereIn('user_id', $ids)
            ->whereNull('deleted_at')
            ->groupBy('user_id')
            ->pluck(DB::raw('count(*)'), 'user_id');
    }

    private function summary(): object
    {
        return DB::table('comments')
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as day', [now()->subDay()])
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as week', [now()->subDays(7)])
            ->selectRaw('coalesce(sum(youtube_comment_id is not null), 0) as youtube')
            ->selectRaw('coalesce(sum(parent_id is not null), 0) as replies')
            ->selectRaw('coalesce(sum(published is null), 0) as unpublished')
            ->first();
    }

    /** Najživšie diskusie za posledný týždeň. */
    private function hotPosts(string $source, int $limit = 5)
    {
        return DB::table('comments')
            ->join('posts', 'posts.id', '=', 'comments.commentable_id')
            ->where('comments.commentable_type', Post::class)
            ->whereNull('comments.deleted_at')
            ->whereNotNull('comments.published')
            ->when($source !== 'all', fn ($query) => $this->source($query, $source))
            ->whereNull('posts.deleted_at')
            ->where('comments.created_at', '>=', now()->subDays(7))
            ->groupBy('posts.id', 'posts.title', 'posts.slug')
            ->orderByDesc('recent')
            ->limit($limit)
            ->get([
                'posts.id',
                'posts.title',
                'posts.slug',
                DB::raw('count(*) as recent'),
                DB::raw('max(comments.created_at) as last_at'),
            ]);
    }
    private function source($query, string $source)
    {
        if ($source === 'youtube') {
            return $query->where(function ($query) {
                $query->whereNotNull('comments.youtube_comment_id')
                    ->orWhere(function ($query) {
                        $query->where('comments.user_id', CommentSync::USER_ID)
                            ->where('comments.user_avatar', 'like', 'https://yt3.%');
                    });
            });
        }
        return $query->whereNull('comments.youtube_comment_id')
            ->whereNotNull('comments.user_id')
            ->where('comments.user_id', '!=', CommentSync::USER_ID);
    }

}
