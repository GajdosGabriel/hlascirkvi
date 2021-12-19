<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\PostSaveRequest;

class OrganizationPostController extends Controller
{
    // public function __construct()
    // {
    //     $this->authorizeResource(Organization::class, 'organization');
    // }

    public function index(Organization $organization)
    {
        $this->authorize('viewAny', $organization);
        $posts = $organization->posts()->latest()->paginate(30);

        return view('profiles.posts.index', compact('posts', 'organization'));
    }

    public function create(Organization $organization) 
    {
        $this->authorize('viewAny', $organization);
        return view('posts.create', ['post' => new Post, 'organization' => $organization]);
    }

    public function edit(Organization $organization, Post $post)
    {
        $this->authorize('viewAny', $organization);
        $this->authorize('update', $post);
        return view('posts.edit', compact('post', 'organization'));
    }

    public function update(Organization $organization, Post $post,  PostSaveRequest $request)
    {
        $post = $request->update($post->id);
        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function store(Organization $organization, PostSaveRequest $request)
    {
       $post = $request->save($organization);
        return redirect()->route('organization.post.index', [$organization->id]);
    }

    public function destroy(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        $post->delete();
        // return redirect('/')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
