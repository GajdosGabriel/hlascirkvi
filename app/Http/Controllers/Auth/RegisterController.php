<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepository;
use App\Rules\IsHuman;
use App\Services\EmailSanitizer;
use App\Support\HumanCheck;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        register as protected registerUser;
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected UserRepository $user;

    public function __construct(UserRepository $user)
    {
        $this->middleware('guest');
        $this->user = $user;
    }

    /**
     * App\Services\EmailSanitizer vie opraviť preklepy v doméne (@gmail.con,
     * @seznma.cz a ďalších stovka) aj medzery navyše, len sa doteraz nemal kde
     * uplatniť — vo validator() nebolo ako podstrčiť opravenú adresu ďalej,
     * ako hovorí aj poznámka, ktorá tam po ňom zostala. Prepíšeme ju teda
     * rovno v požiadavke: s opravenou adresou potom pracuje aj kontrola
     * jedinečnosti, aj založenie účtu, aj overovací e-mail.
     */
    public function register(Request $request)
    {
        $request->merge([
            'email' => (new EmailSanitizer((string) $request->input('email')))->getSanitized(),
        ]);

        return $this->registerUser($request);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email:rfc,dns|max:100|unique:users',
            // Namiesto holého min:6 aj kontrola v zozname uniknutých hesiel
            // (haveibeenpwned). Keď služba neodpovie, Laravel heslo prepustí,
            // takže výpadok neposkladá registráciu.
            'password' => ['required', 'confirmed', Password::min(8)->uncompromised()],
            // Miesto počítania „7 plus 3" — App\Support\HumanCheck.
            HumanCheck::STAMP => ['required', new IsHuman],
        ], [
            HumanCheck::STAMP.'.required' => 'Formulár nie je kompletný, obnovte stránku a skúste to znova.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Potvrdzovací e-mail sa odtiaľto už neposiela. RegistersUsers vystrelí
        // udalosť Registered a na nej visí poslucháč SendEmailVerificationNotification,
        // ktorý zavolá User::sendEmailVerificationNotification(). Ručné
        // Notification::send() by znamenalo dva rovnaké e-maily.
        return $this->user->createUserRegisterForm($data);
    }

    /**
     * Po registrácii nepúšťame človeka rovno na titulku, ale na stránku
     * s vysvetlením, že mu odišiel overovací e-mail (a s možnosťou poslať ho
     * znova). Prihlásený pritom je — overenie nie je podmienkou vstupu.
     */
    protected function registered(Request $request, $user)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['redirect' => route('verification.notice')], 201);
        }

        return redirect()->route('verification.notice');
    }
}
