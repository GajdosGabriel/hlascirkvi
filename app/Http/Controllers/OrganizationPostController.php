<?php

namespace App\Http\Controllers;

use App\Post;
use App\Organization;
use Illuminate\Http\Request;

class OrganizationPostController extends Controller
{
    public function index(Organization $organization)
    {
        $posts = $organization->posts()->latest()->paginate(30);

        return view('profiles.posts.index', compact('posts', 'organization'));
    }

    public function create(Organization $organization) {
        return view('posts.create', ['post' => new Post, 'organization' => $organization]);
    }

    public function edit(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', compact('post', 'organization'));
    }

    public function destroy(Organization $organization, Post $post)
    {
        $this->authorize('update', $post);
        $post->delete();
        // return redirect('/')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }
}
