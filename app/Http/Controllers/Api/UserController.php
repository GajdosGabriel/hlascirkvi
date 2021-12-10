<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function update( User $user, Request $request)
    {
        $user->update($request->all());
        return new UserResource($user);
    }

}
