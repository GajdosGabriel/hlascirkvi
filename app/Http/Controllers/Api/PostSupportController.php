<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostSupportController extends Controller
{
    public function update(Post $postSupport, Request $request)
    {
        $this->authorize('update', $postSupport);

        // Späť do frontu: príspevok prestane byť zverejnený, zaradenie
        // (`section`) si ponechá — pri ďalšom vydaní pôjde tam, kam patril.
        $postSupport->update(['published_at' => null]);

        return redirect()->route('profile.posts.index')->with(session()->flash('flash', 'Video presunuté do Buffer!'));
    }
}
