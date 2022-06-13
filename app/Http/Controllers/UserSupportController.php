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
        if (!$user->email_verified_at == null) {
            session()->flash('flash', 'Email je autorizovaný! Ďakujeme.');
            return;
        };

        $user->update([
            'email_verified_at' => Carbon::now()
        ]);

        return redirect()->route('posts.index');
    }
}
