<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Services\Canal\SocialAvatar;
use App\Support\EmailMask;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
     * Prihlásenie cez Google Identity Services — rovnaký postup ako v projekte
     * event. Tlačidlo na /login a /register vráti v prehliadači ID token (JWT)
     * a formulár ho pošle sem. Overí ho Google cez tokeninfo, takže stačí
     * GOOGLE_CLIENT_ID; client secret ani redirect URI sa nepoužívajú.
     */
    public function googleAuth(Request $request)
    {
        $googleClientId = (string) config('services.google.client_id');
        if ($googleClientId === '') {
            return $this->loginFailed('Prihlásenie cez Google nie je nastavené.');
        }

        $idToken = $request->input('credential');
        if (! is_string($idToken) || $idToken === '') {
            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        // Výpadok Googlu nesmie skončiť päťstovkou.
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $idToken,
                ]);
        } catch (\Throwable $e) {
            report($e);

            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        $payload = $response->ok() ? $response->json() : null;
        if (! is_array($payload)) {
            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        $audience = (string) ($payload['aud'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $providerId = (string) ($payload['sub'] ?? '');

        // Token vydaný pre inú aplikáciu sa nesmie dať použiť tu.
        if ($audience !== $googleClientId || $email === '' || $providerId === '') {
            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        // Účet sa páruje podľa e-mailu, takže neoverená adresa by znamenala
        // prevzatie cudzieho účtu.
        if (! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $this->loginFailed('E-mailová adresa vo vašom Google účte nie je overená.');
        }

        $firstName = trim((string) ($payload['given_name'] ?? ''));
        $lastName = trim((string) ($payload['family_name'] ?? ''));
        if ($firstName === '') {
            [$firstName, $lastName] = $this->splitName((string) ($payload['name'] ?? ''), $email);
        }

        return $this->completeSocialLogin('google', [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'avatar' => (string) ($payload['picture'] ?? ''),
        ]);
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
     * Obtain the user information from Facebook and other.
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request, $service)
    {
        // Zrušené prihlásenie, vypršaná session so state alebo výpadok
        // poskytovateľa — bez toho by používateľ skončil na päťstovke.
        try {
            $oauth_user = Socialite::driver($service)->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->loginFailed('Prihlásenie sa nepodarilo dokončiť, skúste to znova.');
        }

        if (! $oauth_user->getEmail()) {
            return $this->loginFailed('Poskytovateľ nám neposlal e-mailovú adresu, bez nej sa prihlásiť nedá.');
        }

        [$firstName, $lastName] = $this->splitName((string) $oauth_user->getName(), $oauth_user->getEmail());

        return $this->completeSocialLogin($service, [
            'email' => $oauth_user->getEmail(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'avatar' => (string) $oauth_user->getAvatar(),
        ]);
    }

    /**
     * @param  array{email: string, first_name: string, last_name: string, avatar: string}  $profile
     */
    protected function completeSocialLogin(string $service, array $profile)
    {
        // Existujúca adresa znamená prihlásenie do už založeného účtu, nová
        // adresa registráciu. Pre návštevníka je to jedno tlačidlo, ale mal by
        // vedieť, čo sa práve stalo — najmä keď prišiel z registrácie a účet
        // pod tou adresou už mal.
        if (! $user = User::whereEmail($profile['email'])->first()) {
            $user = $this->user->createUserBySocial($profile);
            $this->attachAvatar($user, $service, $profile['avatar']);

            return $this->loginUser($user, $service, 'Vitajte! Účet je založený a e-mailová adresa overená.');
        }

        // Aj pri prihlásení — účty založené skôr (alebo formulárom) fotku
        // kanála nemajú. Kanál, ktorý avatar už má, SocialAvatar nechá tak.
        $this->attachAvatar($user, $service, $profile['avatar']);

        return $this->loginUser($user, $service, 'Vitajte späť, ste prihlásený.');
    }

    /**
     * Facebook posiela len celé meno. Jednoslovné meno predtým skončilo
     * chybou na $name[1]; bez mena sa použije maskovaná časť e-mailu
     * (meno je verejné, adresa nie).
     */
    protected function splitName(string $name, string $email): array
    {
        $name = trim($name);

        if ($name === '') {
            return [EmailMask::name($email), ''];
        }

        $parts = preg_split('/\s+/u', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    protected function attachAvatar(User $user, string $service, string $url): void
    {
        // Zablokovaný účet sa neprihlási, nemá dôvod mu nič sťahovať.
        if ($service !== 'google' || $user->banned()) {
            return;
        }

        app(SocialAvatar::class)->attach($user, $url);
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
