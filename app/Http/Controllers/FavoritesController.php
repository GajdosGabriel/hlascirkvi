<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use App\Models\Prayer;
use App\Models\Comment;
use App\Http\Requests\FavoriteRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\FavoriteForOwner;
use App\Notifications\Prayer\FavoriteForUsers;
use App\Repositories\Eloquent\EloquentUserRepository;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('update');
    }

    public function update(FavoriteRequest $request, $favorite)
    {


        if ($request->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($request);
        }

        $class = "App\\Models\\{$request->input('model')}";
        $class = new $class;

        $model =  $class->whereId($request->input('model_id'))->first();

        $model->favorite();

        if (request()->expectsJson()) return $model;

        return back();
    }

    public function favoriteUsers(User $user) {
        $user->favorite();
        session()->flash('flash', 'Priateľstvo s autorom!');
        return back();
    }


}
