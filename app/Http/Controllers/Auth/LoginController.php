<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PendingLogin;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LogicException;

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
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate(['email' => 'required|string', 'password' => 'required|string']);
        $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
    }

    /**
     * Po prihlásení vždy nástenka. Výnimkou je len chránená stránka, z ktorej
     * middleware `auth` poslal na prihlásenie — tam vráti redirect()->intended().
     */
    public function redirectTo()
    {
        return route('profile.dashboard');
    }

    /**
     * Neaktívny stav prezradíme iba vtedy, keď sedí aj heslo. Samotná znalosť
     * e-mailu tak nestačí na zistenie interného stavu cudzieho účtu.
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $provider = app(AuthManager::class)->createUserProvider(config('auth.guards.web.provider'));

        if ($provider === null) {
            throw new LogicException('Používateľský provider pre web guard nie je nakonfigurovaný.');
        }
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user && ! User::withTrashed()->whereEmail($credentials['email'])->exists()) {
            $pending = app(PendingLogin::class)->find($credentials['email'], $credentials['password']);
            if ($pending) {
                $request->attributes->set('pending_registration_id', $pending->id);

                return false;
            }
        }

        if ($user && $provider->validateCredentials($user, $credentials) && $user->banned()) {
            $request->attributes->set('inactive_account_message', $user->accountAccessMessage());

            return false;
        }

        return $this->guard()->attempt($credentials, $request->boolean('remember'));
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        if ($id = $request->attributes->get('pending_registration_id')) {
            $this->clearLoginAttempts($request);
            $request->session()->regenerate();
            $request->session()->put('pending_registration', $id);
            $message = 'Pred prihlásením potvrďte svoju e-mailovú adresu. Ak e-mail nemáte, môžete si ho poslať znova.';
            $request->session()->flash('flash', $message);

            return $request->wantsJson()
                ? response()->json(['redirect' => route('register.pending'), 'message' => $message], 202)
                : redirect()->route('register.pending');
        }

        if ($message = $request->attributes->get('inactive_account_message')) {
            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('error', $message);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function authenticated(Request $request, $user): void
    {
        $user->recordLogin('password', $request->ip());
    }
}
