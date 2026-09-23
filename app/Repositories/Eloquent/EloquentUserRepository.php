<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use Hash;
use App\Models\User;
use App\Support\EmailMask;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\Admin\Buffer;
use Illuminate\Support\Facades\Request;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\UserRepository;


class EloquentUserRepository extends AbstractRepository implements UserRepository
{
    public function entity()
    {
        return User::class;
    }

    public function createUserRegisterForm($data)
    {
        return $this->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

    }

    /**
     * @param  array{email: string, first_name: string, last_name: string}  $profile
     *         meno už rozdelené v AuthController (Google ho posiela zvlášť,
     *         Facebook len celé)
     */
    public function createUserBySocial($profile)
    {
        $user = $this->create([
            'first_name' => $profile['first_name'],
            'last_name' => $profile['last_name'],
            'email' => $profile['email'],
            // Heslo bolo Hash::make(rand(8,10)), teda "8", "9" alebo "10" —
            // do účtu z Facebooku sa dalo prihlásiť formulárom len so
            // znalosťou e-mailu. Kto chce heslo, nastaví si ho cez obnovu.
            'password' => Hash::make(Str::random(40)),
        ]);

        // email_verified_at nie je v $fillable (App\Models\User) — cez OAuth je
        // e-mail overený poskytovateľom, takže sa nastaví explicitne. Registrácia
        // cez Google teda žiadny potvrdzovací e-mail neposiela, adresa je
        // overená už pri vzniku účtu (AuthController navyše prijme len účet
        // s email_verified od Googlu).
        $user->markEmailAsVerified();

        return $user;
    }


    /*
     * Komentáre bez registrácie
     */

    public function checkIfUserAccountExist($request) {

        if(auth()->check()) return;

        if (User::whereEmail($request->email)->exists()) {
            // Znalosť e-mailovej adresy nie je dôkazom vlastníctva účtu.
            // Predchádzajúci kód prihlásil anonymného návštevníka priamo do
            // existujúceho účtu bez hesla pri komentári, modlitbe či obľúbení.
            throw ValidationException::withMessages([
                'email' => 'Účet s touto adresou už existuje. Prihláste sa alebo použite obnovu hesla.',
            ]);
        }

        $this->createNewUser($request);

    }

    protected function createNewUser($request)
    {
        $user = new User([
            // Meno sa zobrazuje verejne pri komentári — nie celá časť e-mailu.
            'first_name' => EmailMask::name($request->email),
            'last_name' => '',
            'email' => $request->email,
            // Bolo bcrypt('registracnyformularheslo') — rovnaké heslo pre
            // každého, kto komentoval bez registrácie. Prihlásený je hneď
            // a heslo si môže nastaviť cez obnovu.
            'password' => Hash::make(Str::random(40)),
        ]);
        $user->save();
        \Auth::login($user, true);

        $this->sendConfirmEmail($user);
    }

    protected function sendConfirmEmail($user)
    {
        // Podobu e-mailu aj adresu s podpisom drží User::sendEmailVerificationNotification(),
        // aby existovala jedna cesta pre registráciu aj pre opätovné poslanie.
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    /*
     * Koniec komentárov
     */


    public function usersHasRoleAdmin() {
        return $this->entity->whereHas('roles', function($query) {
            $query->whereId(2);
        })->get();
    }


    /*
    * For newsletter
    * User ktorí majú reálny email
    */

    public function usersEmailable()
    {
      return  $this->entity->whereSendEmail(1);
    }




}
