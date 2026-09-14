<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Services\Canal\SocialAvatar;
use Auth;
use Illuminate\Http\Request;
use Socialite;

class AuthController extends Controller
{
    protected $redirectTo = '/';

    protected UserRepository $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
        \Session::put('backUrl', \URL::previous());
    }

    /**
     * Redirect the user to the Social Provider authentication page.
     *
     * @return Response
     */
    public function redirectToProvider($service)
    {
        return Socialite::driver($service)->redirect();
    }

    /**
     * Obtain the user information from GitHub, Facebook and other.
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request, $service)
    {
        // Zrušené prihlásenie (Google vráti ?error=access_denied), vypršaná
        // session so state alebo výpadok poskytovateľa — bez toho by
        // používateľ skončil na päťstovke.
        try {
            $oauth_user = Socialite::driver($service)->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        if (! $oauth_user->getEmail()) {
            return $this->loginFailed('Poskytovateľ nám neposlal e-mailovú adresu, bez nej sa prihlásiť nedá.');
        }

        // Účet sa páruje podľa e-mailu, takže neoverená adresa by znamenala
        // prevzatie cudzieho účtu. Google overenie posiela výslovne.
        if ($service === 'google' && ! ($oauth_user->user['email_verified'] ?? false)) {
            return $this->loginFailed('E-mailová adresa vo vašom Google účte nie je overená.');
        }

        // Existujúca adresa znamená prihlásenie do už založeného účtu, nová
        // adresa registráciu. Pre návštevníka je to jedno tlačidlo, ale mal by
        // vedieť, čo sa práve stalo — najmä keď prišiel z registrácie a účet
        // pod tou adresou už mal.
        if (! $user = User::whereEmail($oauth_user->getEmail())->first()) {
            $user = $this->user->createUserBySocial($oauth_user);
            $this->attachAvatar($user, $service, $oauth_user);

            return $this->loginUser($user, $service, 'Vitajte! Účet je založený a e-mailová adresa overená.');
        }

        // Aj pri prihlásení — účty založené skôr (alebo formulárom) fotku
        // kanála nemajú. Kanál, ktorý avatar už má, SocialAvatar nechá tak.
        $this->attachAvatar($user, $service, $oauth_user);

        return $this->loginUser($user, $service, 'Vitajte späť, ste prihlásený.');
    }

    protected function attachAvatar(User $user, string $service, $oauth_user): void
    {
        // Zablokovaný účet sa neprihlási, nemá dôvod mu nič sťahovať.
        if ($service !== 'google' || $user->banned()) {
            return;
        }

        app(SocialAvatar::class)->attach($user, $oauth_user->getAvatar());
    }

    protected function loginUser($user, string $service, ?string $message = null)
    {
        if ($user->banned()) {
            return $this->isUserLocked($user);
        }
        Auth::login($user, true);
        $user->recordLogin($service, request()->ip());

        //        if(\Session::has('backUrl'))
        //        {
        //            return redirect(\Session::get('backUrl'));
        //        }
        return redirect('/')->with('flash', $message);

    }

    /**
     * Metóda bola zakomentovaná, ale loginUser() ju volala — blokovaný účet
     * pri prihlásení cez Facebook teda skončil na "Call to undefined method",
     * teda päťstovkou. Hlásenie je rovnaké ako v App\Http\Middleware\CheckBanned.
     */
    protected function isUserLocked($user)
    {
        return $this->loginFailed($user->accountAccessMessage());
    }

    protected function loginFailed(string $message)
    {
        return redirect()->route('login')->with('error', $message);
    }
}
