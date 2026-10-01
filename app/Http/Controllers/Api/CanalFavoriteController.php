<?php

namespace App\Http\Controllers\Api;

use App\Models\Canal;
use App\Rules\IsHuman;
use App\Support\HumanCheck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PendingConfirmation;

class CanalFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('store');
    }

    public function store(Canal $canal, Request $request, PendingConfirmation $confirmation)
    {
        // Skrytý kanál sa tvári ako neexistujúci.
        abort_if($canal->published === null, 404);

        // Neprihlásený: do `users` sa nezapisuje nič, odber čaká na potvrdenie
        // e-mailu (App\Models\PendingFavorite). Bez e-mailu sa odoberať nedá.
        if (auth()->guest()) {
            $email = $request->validate([
                'email' => 'required|email|max:100',
                HumanCheck::STAMP => ['required', new IsHuman],
            ])['email'];

            $confirmation->queueFavorite($canal, $email, $request);

            return response()->json(['pending' => true], 202);
        }

        $favorited = $canal->favorite();

        return response()->json([
            'isFavorited' => $favorited,
            'favoritesCount' => $canal->favorites()->count(),
        ]);
    }

}
