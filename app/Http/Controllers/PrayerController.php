<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePrayerRequest;
use App\Notifications\User\NewPrayer;
use App\Prayer;
use App\Repository\User\UserRegistration;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PrayerController extends Controller
{
    public function index()
    {

        return view('prayer.index');
    }

    public function create()
    {
        return Prayer::latest()->paginate(7);
    }

    public function update(Prayer $modlitby, SavePrayerRequest $request)
    {
        $modlitby->update($request->all());
    }

    public function store(SavePrayerRequest $request){

        if($request->email) {
          (new UserRegistration)->commentCheckIfUserAccountExist($request);
        }

      $prayer = auth()->user()->prayers()->create($request->all() );

        Notification::send(User::role('admin')->get(), new NewPrayer($prayer));
    }

    public function destroy(Prayer $modlitby){
        $modlitby->delete();
    }
}
