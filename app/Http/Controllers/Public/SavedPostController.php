<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * „Uložiť na neskôr". Súkromný zoznam prihláseného čitateľa, nie verejné
 * odporúčanie (FavoriteController).
 */
class SavedPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->savedPosts()
            ->published()
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return view('posts.saved', compact('posts'));
    }

    public function toggle(Request $request, Post $post)
    {
        $changes = $request->user()->savedPosts()->toggle($post->id);

        return response()->json(['saved' => $changes['attached'] !== []]);
    }
}
