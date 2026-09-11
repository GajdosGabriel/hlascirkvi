<?php

namespace App\Http\Controllers\Auth;

use App\Repositories\Contracts\UserRepository;
use Auth;
use App\Role;
use App\Models\User;
use Socialite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        if (!$oauth_user->getEmail()) {
            return $this->loginFailed('Poskytovateľ nám neposlal e-mailovú adresu, bez nej sa prihlásiť nedá.');
        }

        // Účet sa páruje podľa e-mailu, takže neoverená adresa by znamenala
        // prevzatie cudzieho účtu. Google overenie posiela výslovne.
        if ($service === 'google' && !($oauth_user->user['email_verified'] ?? false)) {
            return $this->loginFailed('E-mailová adresa vo vašom Google účte nie je overená.');
        }

        if (!$user = User::whereEmail($oauth_user->getEmail())->first())
        {
            $user = $this->user->createUserBySocial($oauth_user);

            return $this->loginUser($user);
        }

        return $this->loginUser($user);
    }


    protected function loginUser($user)
    {
        if($user->disabled){
            return $this->isUserLocked($user);
        }
        \Auth::login($user, true);

//        if(\Session::has('backUrl'))
//        {
//            return redirect(\Session::get('backUrl'));
//        }
        return redirect('/');

    }

    /**
     * Metóda bola zakomentovaná, ale loginUser() ju volala — blokovaný účet
     * pri prihlásení cez Facebook teda skončil na "Call to undefined method",
     * teda päťstovkou. Hlásenie je rovnaké ako v App\Http\Middleware\CheckBanned.
     */
    protected function isUserLocked($user)
    {
        return $this->loginFailed('Váš účet je blokovaný, kontaktujte administrátora webu.');
    }

    protected function loginFailed(string $message)
    {
        return redirect()->route('login')->with('error', $message);
    }
}
