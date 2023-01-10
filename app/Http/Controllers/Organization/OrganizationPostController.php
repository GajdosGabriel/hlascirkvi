<?php

namespace App\Http\Controllers\Organization;

use App\Models\Post;
use App\Actions\UpdatePost;
use App\Filters\PostFilters;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Contracts\StorePostContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostSaveRequest;
use App\Services\PostService\PostService;

class OrganizationPostController extends Controller
{
    public function __construct()
    {
        // $this->organization = $organization;
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

    public function update(Organization $organization, Post $post,  PostSaveRequest $request, PostService $postService)
    {
        $postService->update($post, $request);
        
        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function store(Organization $organization, PostSaveRequest $request, PostService $postService)
    {
        $postService->store($organization, $request);

        return redirect()->route('organization.post.index', [$organization->id]);
    }

    public function destroy(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        $post->comments()->delete();
        $post->delete();
        // return redirect('/')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
