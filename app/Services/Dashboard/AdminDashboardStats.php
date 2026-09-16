<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Súhrnné čísla za celý web pre úvod administrácie. */
class AdminDashboardStats
{
    public function get(?CarbonImmutable $now = null): array
    {
        $now = $now ?: CarbonImmutable::now();
        $monthAgo = $now->subDays(30);
        $today = $now->startOfDay();

        $posts = DB::table('posts')
            ->where('youtube_blocked', 0)
            ->selectRaw('coalesce(sum(deleted_at is null), 0) as total')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at is not null), 0) as published')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at is null), 0) as waiting')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at >= ?), 0) as published_today', [$today])
            ->selectRaw('coalesce(sum(case when deleted_at is null then count_view end), 0) as views')
            ->first();

        return [
            'now' => $now,
            'users' => (object) [
                'total' => DB::table('users')->whereNull('deleted_at')->count(),
                'new' => DB::table('users')->whereNull('deleted_at')->where('created_at', '>=', $monthAgo)->count(),
            ],
            'canals' => (object) [
                'total' => DB::table('canals')->whereNull('deleted_at')->count(),
                'published' => DB::table('canals')->whereNull('deleted_at')->where('published', 1)->count(),
            ],
            'posts' => $posts,
            'comments' => (object) [
                'total' => DB::table('comments')->whereNull('deleted_at')->count(),
                'new' => DB::table('comments')->whereNull('deleted_at')->where('created_at', '>=', $monthAgo)->count(),
            ],
            'prayers' => (object) [
                'open' => DB::table('prayers')->whereNull('deleted_at')->whereNull('fulfilled_at')->count(),
                'fulfilled' => DB::table('prayers')->whereNull('deleted_at')->whereNotNull('fulfilled_at')->count(),
            ],
        ];
    }
}
