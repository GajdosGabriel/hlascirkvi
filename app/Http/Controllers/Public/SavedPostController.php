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
        $saved = $request->user()->savedPosts();

        if ($saved->whereKey($post->id)->exists()) {
            $saved->detach($post->id);

            return response()->json(['saved' => false]);
        }

        // Pridať sa dá len zverejnený príspevok; odobrať sa dá vždy. Inak by
        // sa v pivote držali riadky, ktoré zoznam (`published()`) nezobrazí.
        abort_unless($post->published_at !== null, 404);

        $saved->attach($post->id);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, int $postId)
    {
        $request->user()->savedPosts()->detach($postId);

        if ($request->expectsJson()) {
            return response()->json(['saved' => false]);
        }

        return back();
    }
}
