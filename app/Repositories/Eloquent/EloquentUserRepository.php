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
use App\Models\PendingRegistration;
use Illuminate\Support\Str;
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

    /**
     * Účet z potvrdenej registrácie (App\Models\PendingRegistration). Adresa
     * je už overená kliknutím na odkaz, heslo prichádza zahashované.
     * email_verified_at sa nastaví ešte pred uložením, aby UserObserver::created
     * rovno založil kanál (App\Services\UserActivation).
     */
    public function createFromPendingRegistration(PendingRegistration $pending): User
    {
        $user = new User([
            'first_name' => $pending->first_name,
            'last_name' => $pending->last_name,
            'email' => $pending->email,
        ]);
        $user->forceFill([
            'password' => $pending->password,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    /**
     * @param  array{email: string, first_name: string, last_name: string}  $profile
     *         meno už rozdelené v AuthController (Google ho posiela zvlášť,
     *         Facebook len celé)
     */
    public function createUserBySocial($profile)
    {
        $user = new User([
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
        // s email_verified od Googlu). Nastavuje sa ešte pred uložením, aby
        // UserObserver::created rovno založil kanál.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }


    /*
     * Komentár, modlitba či „Pripojiť sa" bez registrácie už účet nezakladajú
     * (predtým checkIfUserAccountExist — neoverený riadok v `users` a rovno
     * prihlásenie). Čakajú v App\Models\PendingComment, PendingPrayer
     * a PendingFavorite; overený účet vytvorí až
     * App\Services\UserActivation::verifiedUserFor po kliknutí na odkaz.
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
