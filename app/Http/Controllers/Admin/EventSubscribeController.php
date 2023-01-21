<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventSubscribe;

class EventSubscribeController extends Controller
{
    public function index()
    {
        $items = EventSubscribe::latest()->paginate(40)->withQueryString();
        
        return view('admins.eventSubscribes.index', compact('items'));

    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
    }

}
