<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
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

        // Verified založí účtu kanál (App\Listeners\ActivateVerifiedUser).
        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->route('posts.index')
            ->with('flash', 'Email je autorizovaný! Ďakujeme.');
    }
}
