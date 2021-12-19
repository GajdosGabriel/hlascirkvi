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
        $oauth_user = Socialite::driver($service)->user();

        if (!$user = User::whereEmail($oauth_user->email)->first())
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


//
//    public static function isUserLocked(User $user)
//    {
//        flash()->error('Váš účet je blokovaný! Kontaktujte administrátora');
//        return redirect('/login');
//    }
}
