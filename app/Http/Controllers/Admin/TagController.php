<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class TagController extends Controller
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
