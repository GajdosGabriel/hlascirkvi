<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Prayer;
use Illuminate\Http\Request;
use App\Notifications\Prayer\NewPrayer;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\ConfirmFulfilledPrayer;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Http\Controllers\Controller;

class PrayerController extends Controller
{
    public function index()
    {
        return view('prayers.index');
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
