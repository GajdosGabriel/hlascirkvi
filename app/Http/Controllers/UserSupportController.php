<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;


class UserSupportController extends Controller
{
    public function __construct()
    {
       //
    }



    public function setDenominationSession(Request $request)
    {
        session()->put('denomination', $request->denomination);

        // set denomination if auth user
        if (auth()->check()) {
            auth()->user()->update([
                'set_denomination' => $request->denomination
            ]);
        }


        if ($request->denomination == 0) {
            session()->forget('denomination');
        }
        return back();
    }

    public function confirmEmail(User $user)
    {
        // Pôvodne `!$user->email_verified_at == null` — `!` sa vyhodnotí skôr
        // než `==`, takže podmienka robila presný opak. A vetva pre už overený
        // e-mail nevracala odpoveď, čiže návštevník videl prázdnu stránku.
        if ($user->email_verified_at !== null) {
            return redirect()->route('posts.index')
                ->with('flash', 'Email je už autorizovaný! Ďakujeme.');
        }

        // email_verified_at nie je v $fillable (App\Models\User).
        $user->email_verified_at = Carbon::now();
        $user->save();

        return redirect()->route('posts.index')
            ->with('flash', 'Email je autorizovaný! Ďakujeme.');
    }
}
