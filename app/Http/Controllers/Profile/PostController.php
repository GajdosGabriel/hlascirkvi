<?php

namespace App\Http\Controllers\Profile;

use App\Models\Post;
use App\Filters\PostFilters;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostSaveRequest;
use App\Services\PostService\PostService;

class PostController extends Controller
{

    public function __construct(private PostService $postService)
    {
        $this->authorizeResource(Post::class, 'post');
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(PostFilters $filters)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();

        $posts = $organization->posts()->filter($filters)->latest()->paginate(30);

        return view('profiles.posts.index', compact('posts', 'organization'));
    }

    public function create()
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        return view('posts.create', ['post' => new Post, 'organization' => $organization]);
    }

    public function edit(Post $post)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $this->authorize('update', $post);
        return view('posts.edit', compact('post', 'organization'));
    }

    public function update(Post $post,  PostSaveRequest $request)
    {
        $this->postService->update($post, $request);

        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function store(PostSaveRequest $request)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $this->postService->store($organization, $request);

        return redirect()->route('profile.post.index', [$organization->id]);
    }

    // Zmazať alebo obnoviť Post
    public function destroy($post)
    {
        $this->authorize('update', $post);

        $organization  = Organization::where('id', auth()->user()->org_id)->first();

        $post = Post::withTrashed()->find($post);

        if ($post->deleted_at) {
            $post->restore();
            $post->comments()->restore();
            return redirect()->route('profile.post.index')->with(session()->flash('flash', 'Príspevok bol obnovený!'));
        } else {
            $post->comments()->delete();
            $post->delete();
        }

        return redirect()->route('profile.post.index')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
