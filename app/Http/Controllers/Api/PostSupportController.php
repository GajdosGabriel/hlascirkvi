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

        $postSupport->updaters()->detach();
        return redirect()->route('profile.canals.posts.index', $postSupport->organization_id)->with(session()->flash('flash', 'Video presunuté do Buffer!'));
    }
}
