<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\BigThingsRequest;

class PostThingController extends Controller
{
    public function store(Post $post, BigThingsRequest $request)
    {
        $post->addBigThink($request->except('iamHuman'));
        return back();
    }
}
