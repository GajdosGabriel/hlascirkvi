<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Jediné, čo sem frontend posiela, je čas posledného otvorenia zvončeka
     * (resources/js/navigation/Bell.vue:103). Predtým tu bolo
     * `$user->update($request->all())` nad modelom s $guarded = [], takže
     * ktorýkoľvek prihlásený užívateľ vedel prepísať komukoľvek heslo,
     * org_id či email_verified_at.
     */
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $user->update($request->validate([
            'notify_bell' => 'required|date',
        ]));

        return new UserResource($user);
    }
}
