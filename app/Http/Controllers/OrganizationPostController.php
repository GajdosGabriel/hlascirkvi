<?php

namespace App\Http\Controllers;

use App\Post;
use App\Organization;
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

    public function create(Organization $organization) {
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
       $post = $request->save();
        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function destroy(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        $post->delete();
        // return redirect('/')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
