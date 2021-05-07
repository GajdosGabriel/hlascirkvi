<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;


use App\Notifications\Admin\Buffer;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\UserRepository;
use App\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Str;

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
       $name = explode(" ", $value->name);
        $firstName = $name[1];
        $lastName = $name[0];

//        dd($name[0]);

       return $this->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $value->email,
                'password' => Hash::make(rand(8,10)),
                'email_verified_at' => Carbon::now()
                ]);
    }


    /*
     * Komentáre bez registrácie
     */

    public function commentCheckIfUserAccountExist($request) {

        if(auth()->check()) return;

        if($user = User::whereEmail($request->email)->first() )
            return \Auth::login($user, true);

        $this->createNewUser($request);

    }

    protected function createNewUser($request)
    {
        $user = new User([
            'first_name' => strstr($request->email, '@', true),
            'email' => $request->email,
            'password' => bcrypt('registracnyformularheslo'),
        ]);
        $user->save();
        \Auth::login($user, true);
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
    */

    public function usersEmailable()
    {
      return  $this->entity->whereSendEmail(1);
    }




}
