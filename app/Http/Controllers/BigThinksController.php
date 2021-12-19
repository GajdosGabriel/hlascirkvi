<?php

namespace App\Http\Controllers;

use App\Http\Requests\BigThingsRequest;
use App\Models\Post;
use App\Http\Requests\SaveCommentsRequest;
use Illuminate\Http\Request;

class BigThinksController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function store(BigThingsRequest $request, Post $post)
    {
       $post->addBigThink($request->except('iamHuman'));
        return back();
    }
}
