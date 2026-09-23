<?php

namespace App\Http\Controllers\Api;

use App\Models\Canal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Services\PendingConfirmation;

class CanalFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('store');
    }

    public function store(Canal $canal, Request $request, PendingConfirmation $confirmation)
    {
        // Neprihlásený: do `users` sa nezapisuje nič, odber čaká na potvrdenie
        // e-mailu (App\Models\PendingFavorite). Bez e-mailu sa odoberať nedá.
        if (auth()->guest()) {
            $email = $request->validate(['email' => 'required|email|max:100'])['email'];

            $confirmation->queueFavorite($canal, $email, $request);

            return response()->json(['pending' => true], 202);
        }

        return new FavoriteResource($canal->favorite());
    }

}
