<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Prayer;
use App\Models\PendingPrayer;
use App\Services\PendingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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

    /**
     * Odkaz z e-mailu (App\Notifications\Prayer\PrayerFulfilledOrNotYet) len
     * ukáže potvrdzovaciu stránku — poštové skenery odkazy otvárajú samy
     * a modlitbu by označili bez vedomia autora.
     */
    public function showFulfilled(Prayer $prayer)
    {
        return view('prayers.fulfilled', [
            'prayer' => $prayer,
            'action' => URL::signedRoute('prayer.fulfilledAt', ['prayer' => $prayer->getKey()]),
        ]);
    }

    /**
     * POST z potvrdzovacej stránky. Opakované potvrdenie už neprepíše dátum
     * vypočutia ani znova nerozpošle oznámenie administrátorom.
     */
    public function fulfilledAt(Prayer $prayer)
    {
        if ($prayer->fulfilled_at === null) {
            $prayer->update(['fulfilled_at' => Carbon::now()]);

            Notification::send(User::role('admin')->get(), new ConfirmFulfilledPrayer($prayer));
        }

        return redirect()->route('modlitby.index')
            ->with('flash', 'Modlitba bola označená ako vypočutá. Ďakujeme.');
    }
}
