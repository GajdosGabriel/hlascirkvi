<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Services\Form;
use App\Models\Messenger;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('search');
    }

    public function index()
    {
        $users = User::all();

        if (request()->expectsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }



    public function update( User $user, Request $request)
    {
        $user->update($request->all());
        return back();
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