<?php

namespace App\Services;

use App\Models\Canal;

class CanalOverview
{
    public function load(Canal $canal): void
    {
        $canal->load(['village:id,fullname', 'users:id,first_name,last_name,email'])
            ->loadCount([
                'posts', 'prayers', 'seminars', 'favorites',
                'posts as published_posts_count' => fn ($query) => $query->published(),
                'posts as unpublished_posts_count' => fn ($query) => $query->unpublished(),
                'posts as deleted_posts_count' => fn ($query) => $query->onlyTrashed(),
            ])
            ->loadMax('posts', 'created_at');
    }
}
