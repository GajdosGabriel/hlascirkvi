<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class RegistredUsersController extends Controller
{
    public function update(User $registredUser, Request $request)
    {
        $registredUser->update($request->all());

        return $registredUser;
    }
}
