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
}
