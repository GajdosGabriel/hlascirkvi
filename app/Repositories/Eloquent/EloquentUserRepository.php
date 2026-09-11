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
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Notifications\Admin\Buffer;
use Illuminate\Support\Facades\Request;
use App\Notifications\User\ConfirmEmail;
use App\Repositories\AbstractRepository;
use Illuminate\Support\Facades\Notification;
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

    public function createUserBySocial($value)
    {
        [$firstName, $lastName] = $this->splitSocialName($value);

        $user = $this->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $value->getEmail(),
            // Heslo bolo Hash::make(rand(8,10)), teda "8", "9" alebo "10" —
            // do účtu z Facebooku sa dalo prihlásiť formulárom len so
            // znalosťou e-mailu. Kto chce heslo, nastaví si ho cez obnovu.
            'password' => Hash::make(Str::random(40)),
        ]);

        // email_verified_at nie je v $fillable (App\Models\User) — cez OAuth je
        // e-mail overený poskytovateľom, takže sa nastaví explicitne.
        $user->email_verified_at = Carbon::now();
        $user->save();

        return $user;
    }

    /**
     * Google posiela meno a priezvisko zvlášť (given_name/family_name),
     * Facebook len celé meno. To sa predtým bralo ako "Priezvisko Meno"
     * a jednoslovné meno skončilo chybou na $name[1].
     */
    protected function splitSocialName($value): array
    {
        $raw = $value->user ?? [];

        if (!empty($raw['given_name'])) {
            return [$raw['given_name'], $raw['family_name'] ?? ''];
        }

        $name = trim((string) $value->getName());

        if ($name === '') {
            return [Str::before((string) $value->getEmail(), '@'), ''];
        }

        $parts = preg_split('/\s+/u', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }


    /*
     * Komentáre bez registrácie
     */

    public function checkIfUserAccountExist($request) {

        if(auth()->check()) return;

        if($user = User::whereEmail($request->email)->first() )
            return \Auth::login($user, true);

        $this->createNewUser($request);

    }

    protected function createNewUser($request)
    {
        $user = new User([
            'first_name' => strstr($request->email, '@', true),
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
        if( $user->email_verified_at == null) {
            Notification::send($user, new ConfirmEmail($user));
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
