<?php

namespace App\Http\Controllers\Events;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Repositories\Eloquent\EloquentUserRepository;

class EventFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('store');
    }

    public function store(Event $event, Request $request)
    {
        if ($request->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($request);
        }

        $event->favorite();
        return back();
    }
}
