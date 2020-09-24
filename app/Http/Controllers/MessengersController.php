<?php

namespace App\Http\Controllers;

use App\Messenger;
use App\Organization;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessengerRequest;
use App\Notifications\Messengers;

class MessengersController extends Controller
{


    public function toAdmin(StoreMessengerRequest $request) {

       Messenger::create([
            'user_id' => $request->input('user_id', 1),
            'requested_organization' => $request->input('requested_organization', 1),
            'body' => $request->input('body')
        ]);

        return back();
    }

    public function store(StoreMessengerRequest $request, Organization $organization) {

       $message = Messenger::create([
            'user_id' => auth()->user()->id,
            'requested_organization' => $organization->id,
            'body' => $request->input('body')
        ]);

        if(request()->expectsJson()) {
            return $message;
        };

        return back();
    }


}
