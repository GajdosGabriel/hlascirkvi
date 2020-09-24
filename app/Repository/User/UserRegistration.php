<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 19.09.2018
 * Time: 13:55
 */

namespace App\Repository\User;

use App\User;



class UserRegistration
{


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




}