<?php

namespace App\Http\Controllers;

use App\User;
use App\Prayer;
use Illuminate\Http\Request;
use App\Notifications\Prayer\NewPrayer;
use App\Http\Requests\SavePrayerRequest;
use Illuminate\Support\Facades\Notification;
use App\Repositories\Eloquent\EloquentUserRepository;

class PrayerController extends Controller
{
    public function index()
    {

        return view('prayer.index');
    }

    public function create()
    {
        //
    }

    public function update(Prayer $modlitby, SavePrayerRequest $request)
    {
        $modlitby->update($request->all());
    }

    public function store(SavePrayerRequest $request)
    {
        if($request->email) {
            (new EloquentUserRepository)->commentCheckIfUserAccountExist($request);
        }

      $prayer = auth()->user()->prayers()->create($request->all() );

        Notification::send(User::role('admin')->get(), new NewPrayer($prayer));
    }

    public function destroy(Prayer $modlitby)
    {
        $modlitby->delete();
    }
}
