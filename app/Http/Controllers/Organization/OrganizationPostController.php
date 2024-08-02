<?php

namespace App\Http\Controllers\Organization;

use App\Models\Post;
use App\Filters\PostFilters;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostSaveRequest;
use App\Services\PostService\PostService;

class OrganizationPostController extends Controller
{

    public function __construct(private PostService $postService)
    {
        $this->authorizeResource(Post::class, 'post');
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(Organization $organization, PostFilters $filters)
    {
        $posts = $organization->posts()->filter($filters)->latest()->paginate(30);

        return view('profiles.posts.index', compact('posts', 'organization'));
    }

    public function create(Organization $organization)
    {
        return view('posts.create', ['post' => new Post, 'organization' => $organization]);
    }

    public function edit(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', compact('post', 'organization'));
    }

    public function update(Organization $organization, Post $post,  PostSaveRequest $request)
    {
        $this->postService->update($post, $request);

        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function store(Organization $organization, PostSaveRequest $request)
    {
        $this->postService->store($organization, $request);

        return redirect()->route('organization.post.index', [$organization->id]);
    }

    // Zmazať alebo obnoviť Post
    public function destroy(Organization $organization, $post)
    {
        $this->authorize('update', $post);

        $post = Post::withTrashed()->find($post);

        if ($post->deleted_at) {
            $post->restore();
            $post->comments()->restore();
            return redirect()->route('organization.post.index', $organization->id)->with(session()->flash('flash', 'Príspevok bol obnovený!'));
        } else {
            $post->comments()->delete();
            $post->delete();
        }

        return redirect()->route('organization.post.index', $organization->id)->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
