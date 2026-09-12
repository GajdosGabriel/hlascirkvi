<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

/**
 * Overenie e-mailovej adresy.
 *
 * Oproti Illuminate\Foundation\Auth\VerifiesEmails je tu jeden rozdiel:
 * samotné potvrdenie (verify) nevyžaduje prihlásenie. Odkaz z e-mailu si
 * ľudia otvárajú v mobile alebo v inom prehliadači než v tom, kde sa
 * registrovali, a Laravelova verzia ich tam odbije na prihlasovanie.
 * Autorizáciu preto nesie výhradne podpis v URL (middleware `signed`)
 * spolu s odtlačkom adresy — viď App\Notifications\User\ConfirmEmail.
 */
class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['notice', 'resend']);
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only(['verify', 'resend']);
    }

    /**
     * Stránka „pozrite si schránku" hneď po registrácii.
     */
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('posts.index')
                ->with('flash', 'Vaša e-mailová adresa je už potvrdená.');
        }

        return view('auth.verify');
    }

    public function verify(Request $request, User $user, string $hash)
    {
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('posts.index')
                ->with('flash', 'E-mailová adresa je už potvrdená. Ďakujeme!');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Účet je overený, takže návštevníka rovno pustíme dnu — ale len ak
        // tam ešte nie je niekto iný, inak by odkaz z e-mailu prepol
        // prihláseného človeka na cudzí účet.
        if (! $request->user() && ! $user->banned()) {
            auth()->login($user, true);
        }

        return redirect()->route('posts.index')
            ->with('flash', 'Ďakujeme, e-mailová adresa je potvrdená.');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('posts.index')
                ->with('flash', 'Vaša e-mailová adresa je už potvrdená.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('flash', 'Potvrdzovací e-mail sme poslali znova.');
    }
}
