<?php

namespace App\Http\Controllers;

use App\Messenger;
use App\Organization;
use App\Services\Form;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('search');
    }

    public function index() {
        $users = User::all();

        if(request()->expectsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users'));
    }

    public function show(Organization $organization, $slug)
    {
        return view('posts.index', ['posts' => $organization->posts()->latest()->paginate(), 'organization' => $organization]);
    }

    public function profile(Organization $organization, $slug) {

//        $this->authorize('update', $user, auth());

        $messages = (new Messenger)->scopeUserMessages($organization->id);

        return view('organizations.profile',compact(['organization', 'messages']));
    }



    public function userNotifications() {
        return auth()->user()->unreadNotifications()->take(7)->get();
//        return auth()->user()->unreadNotifications()->get();
    }

    public function markAsRead($id) {
        return auth()->user()->notifications()->findOrFail($id)->markAsRead();
    }

    public function setDenominationSession (Request $request) {
       session()->put('denomination', $request->denomination);

        // set denomination if auth user
        if(auth()->check()) {
           auth()->user()->update([
                'set_denomination' => $request->denomination
           ]);
        }


        if($request->denomination == 0) {
            session()->forget('denomination');
        }
        return back();
    }

    public function confirmEmail(User $user)
    {
        $user->update([
            'email_verified_at' => Carbon::now()
        ]);

        return redirect()->route('posts.index');

    }









}
