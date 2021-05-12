<?php

namespace App\Http\Controllers;

use App\User;
use App\Prayer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\SavePrayerRequest;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\ConfirmFulfilledPrayer;
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
        if ($request->email) {
            (new EloquentUserRepository)->commentCheckIfUserAccountExist($request);
        }

        $prayer = auth()->user()->prayers()->create($request->all());
    }

    public function destroy(Prayer $modlitby)
    {
        $modlitby->delete();
    }


    public function fulfilledAt(Prayer $prayer)
    {
        $prayer->update([
            'fulfilled_at' => Carbon::now()
        ]);

        session()->flash('flash', 'Modliba bola označená ako vypočutá. Ďakujeme.');

        Notification::send(User::role('admin')->get(), new ConfirmFulfilledPrayer($prayer));

        return redirect()->route('modlitby.index');
    }
}
