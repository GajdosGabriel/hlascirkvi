<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Post;
use Illuminate\Support\Str;

class TagsController extends Controller
{

    public function index()
    {
       $tags = Tag::all();

        return view('admins.tags', compact('tags'));

    }

    public function store(Request $request)
    {
        Tag::create($request->all());

        return  back();
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
    }


}
