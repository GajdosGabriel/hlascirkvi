<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\SystemLog\Recorder;
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
            $this->log($request, $post, false);

            return response()->json(['saved' => false]);
        }

        // Pridať sa dá len zverejnený príspevok; odobrať sa dá vždy. Inak by
        // sa v pivote držali riadky, ktoré zoznam (`published()`) nezobrazí.
        abort_unless($post->published_at !== null, 404);

        $saved->attach($post->id);
        $this->log($request, $post, true);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, int $postId)
    {
        // Zo zoznamu sa dá odobrať aj príspevok, ktorý sa medzitým skryl či zmazal.
        if ($request->user()->savedPosts()->detach($postId)) {
            $this->log($request, Post::withoutGlobalScopes()->find($postId), false);
        }

        if ($request->expectsJson()) {
            return response()->json(['saved' => false]);
        }

        return back();
    }

    /**
     * Uloženie je často jediné, čo čitateľ po registrácii urobí — bez záznamu
     * v denníku by pri účte nebolo vidno nič okrem prihlásenia.
     */
    private function log(Request $request, ?Post $post, bool $saved): void
    {
        Recorder::activity('post', $saved ? 'saved' : 'unsaved',
            ($saved ? 'Uložené na neskôr: ' : 'Odobraté z uložených: ') . ($post?->title ?? 'zmazaný príspevok'),
            $post, (int) $request->user()->getKey(),
        );
    }
}
