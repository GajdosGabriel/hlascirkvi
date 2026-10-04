<?php

namespace App\Http\Controllers\Seminars;

use App\Models\Post;
use App\Models\Seminar;
use App\Http\Controllers\Controller;

class SeminarPostController extends Controller
{
    public function show(Seminar $seminar, Post $post)
    {
        abort_unless($seminar->posts()->whereKey($post->id)->exists(), 404);

        return redirect()->route('post.show', [$post->id, $post->slug]);
    }
}