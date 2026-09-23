<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Rules\IsHuman;
use App\Services\EmailSanitizer;
use App\Services\SystemLog\Recorder;
use App\Support\HumanCheck;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Registrácia formulárom v dvoch krokoch.
 *
 * 1. Formulár založí len App\Models\PendingRegistration a pošle odkaz na
 *    zadanú adresu. V `users` zatiaľ nič nie je — žiadny účet, kanál ani
 *    správa adminom. Vymyslené a robotické registrácie tu zostanú visieť
 *    a po expirácii ich zmaže model:prune.
 * 2. Až kliknutie na odkaz (confirm) vytvorí skutočný, rovno overený účet
 *    a UserObserver::created mu založí kanál.
 *
 * Trait RegistersUsers z laravel/ui sa už nepoužíva — ten zakladal účet
 * a prihlasoval hneď po odoslaní formulára.
 */
class RegisterController extends Controller
{
    /** Kľúč v session, podľa ktorého stránka „pozrite si schránku" vie, o koho ide. */
    protected const SESSION_KEY = 'pending_registration';

    public function __construct(protected UserRepository $user)
    {
        $this->middleware('guest')->except('confirm');
        // Jedna IP adresa nemá čo zakladať desiatky registrácií za hodinu.
        $this->middleware('throttle:20,60')->only('register');
        $this->middleware('throttle:6,1')->only(['resend', 'confirm']);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // App\Services\EmailSanitizer opraví preklepy v doméne (@gmail.con…)
        // ešte pred validáciou, takže s opravenou adresou pracuje aj kontrola
        // jedinečnosti, aj potvrdzovací e-mail.
        $request->merge([
            'email' => (new EmailSanitizer((string) $request->input('email')))->getSanitized(),
        ]);

        $data = $this->validator($request->all())->validate();

        $pending = PendingRegistration::firstOrNew(['email' => $data['email']]);
        // Záznam, ktorému už vypršal odkaz (a model:prune ho ešte nezmazal),
        // sa berie ako nová registrácia.
        $isResend = $pending->exists && $pending->expires_at?->isFuture();

        $pending->forceFill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'password' => Hash::make($data['password']),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ])->save();

        // Opakované odoslanie formulára s tou istou adresou nepošle ďalší
        // e-mail skôr, než je dovolené — inak by sa dal formulár použiť na
        // zasypanie cudzej schránky.
        $pending->sendConfirmation(isResend: $isResend);

        Recorder::info('auth', 'registration_pending', 'Registrácia čaká na potvrdenie e-mailu',
            status: 'ok',
            recipient: $pending->email,
            subject: $pending,
            ip: $request->ip(),
        );

        $request->session()->put(self::SESSION_KEY, $pending->id);

        if ($request->wantsJson()) {
            return new JsonResponse(['redirect' => route('register.pending')], 202);
        }

        return redirect()->route('register.pending');
    }

    /** Stránka „pozrite si schránku" hneď po odoslaní formulára. */
    public function pending(Request $request)
    {
        $pending = $this->pendingFromSession($request);

        if (! $pending) {
            return redirect()->route('register');
        }

        return view('auth.register-pending', ['email' => $pending->email]);
    }

    public function resend(Request $request)
    {
        $pending = $this->pendingFromSession($request);

        if (! $pending) {
            return redirect()->route('register')
                ->with('flash', 'Platnosť registrácie vypršala, vyplňte prosím formulár znova.');
        }

        if (! $pending->sendConfirmation(isResend: true)) {
            return back()->with('flash', 'E-mail sme poslali len nedávno. Počkajte prosím pár minút a pozrite aj nevyžiadanú poštu.');
        }

        return back()->with('flash', 'Potvrdzovací e-mail sme poslali znova.');
    }

    /**
     * Odkaz z e-mailu. Až tu vzniká skutočný účet (a s ním kanál).
     *
     * Nie je za `guest` — odkaz sa otvára aj tam, kde je niekto prihlásený.
     * Toho však neprepíname na nový účet.
     */
    public function confirm(Request $request, string $token)
    {
        $pending = PendingRegistration::findByToken($token);

        if (! $pending) {
            return redirect()->route('login')
                ->with('flash', 'Odkaz už bol použitý alebo mu vypršala platnosť. Ak ste účet už potvrdili, stačí sa prihlásiť.');
        }

        // Medzitým mohol účet s rovnakou adresou vzniknúť inak (Google,
        // komentár bez registrácie). Ten nepremazávame.
        if (User::withTrashed()->whereEmail($pending->email)->exists()) {
            $pending->delete();

            return redirect()->route('login')
                ->with('flash', 'Účet s touto adresou už existuje. Prihláste sa, prípadne použite obnovu hesla.');
        }

        $user = DB::transaction(function () use ($pending) {
            $user = $this->user->createFromPendingRegistration($pending);
            $pending->delete();

            return $user;
        });

        // Pre denník udalostí. Laravelov SendEmailVerificationNotification
        // na nej visí tiež, ale overenému účtu nič neposiela.
        event(new Registered($user));

        $request->session()->forget(self::SESSION_KEY);

        if (! $request->user()) {
            auth()->login($user, true);
        }

        return redirect()->route('posts.index')
            ->with('flash', 'Vitajte! Registrácia je dokončená a účet je pripravený.');
    }

    protected function pendingFromSession(Request $request): ?PendingRegistration
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return $id ? PendingRegistration::where('expires_at', '>', now())->find($id) : null;
    }

    /**
     * @param  array<string, mixed>  $data
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
}
