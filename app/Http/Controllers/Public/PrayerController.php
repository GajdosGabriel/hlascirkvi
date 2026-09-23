<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Prayer;
use App\Models\PendingPrayer;
use App\Services\PendingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\ConfirmFulfilledPrayer;
use App\Http\Controllers\Controller;

class PrayerController extends Controller
{
    public function index()
    {
        return view('prayers.index');
    }

    /**
     * Odkaz z e-mailu (App\Notifications\Prayer\ConfirmPrayer). Až tu vzniká
     * účet — rovno overený, do `users` iný nepatrí — a čakajúce modlitby
     * s touto adresou sa presunú do `prayers` a zverejnia.
     *
     * Kliknutím je adresa preukázaná, preto neprihláseného prihlásime.
     * Toho, kto je už prihlásený, na iný účet neprepíname.
     */
    public function confirm(Request $request, string $token, PendingConfirmation $confirmation)
    {
        $pending = PendingPrayer::findByToken($token);

        if (! $pending) {
            return redirect()->route('modlitby.index')
                ->with('flash', 'Odkaz už bol použitý alebo mu vypršala platnosť.');
        }

        if (! $user = $confirmation->confirm($pending)) {
            return redirect()->route('modlitby.index')
                ->with('flash', 'Účet s touto adresou nie je aktívny, modlitbu preto nemožno zverejniť.');
        }

        if (! $request->user()) {
            auth()->login($user, true);
        }

        return redirect()->route('modlitby.index')
            ->with('flash', 'Ďakujeme, adresa je potvrdená a modlitba je zverejnená.');
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
