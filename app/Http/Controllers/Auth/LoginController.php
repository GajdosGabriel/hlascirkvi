<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Kam po prihlásení.
     *
     * Predtým sa v konštruktore ukladalo URL::previous() do session — teda pri
     * každej akcii vrátane samotného POST /login — a redirectTo() naň potom
     * presmeroval. URL::previous() číta hlavičku Referer, ktorú si nastavuje
     * klient, takže z toho bol otvorený redirect na cudziu doménu.
     *
     * Berieme preto len adresu v rámci vlastnej domény, uloženú pri zobrazení
     * prihlasovacieho formulára.
     */
    public function showLoginForm()
    {
        $previous = \URL::previous();

        if ($previous && str_starts_with($previous, config('app.url'))) {
            \Session::put('backUrl', $previous);
        } else {
            \Session::forget('backUrl');
        }

        return view('auth.login');
    }

    public function redirectTo()
    {
        $backUrl = \Session::pull('backUrl');

        if ($backUrl && str_starts_with($backUrl, config('app.url'))) {
            return $backUrl;
        }

        return $this->redirectTo;
    }
}
