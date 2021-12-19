<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AddresBookController extends Controller
{
    public function importContacts(User $user)
    {
        $users = $user->addresBooks()->latest()->paginate(50);
        return view('profiles.import_contacts', ['user' => $user, 'users' => $users]);
    }


    public function storeUsersContact(User $user, Request $request) {

        $string = trim($request->input('body'));

        // Match e-mail addresses.
        $regex = '/([A-Za-z0-9._-]+@[A-Za-z0-9._+-]+\.[A-Za-z]{2,4})/';
        preg_match_all($regex, $string, $emails);

        // Remove duplicates and formats array
        $emails = array_values(array_unique($emails[0]));

        // count insert emails
        $count = count($emails);
        $imported = 0;

        // Extract names from e-mail addresses.
        foreach($emails as $email) {
            $regex = '/([A-Za-z0-9._-]+)(?=@)/';
            preg_match($regex, $email, $name);

            // If you need, then create new friend
            if( $user->addresBooks()->where('email', $email)->first()) continue;

            $user->addresBooks()->create([
                'email'   => $email,
                'first_name'    => $name[0],
                'last_name'    => 'Neuvedené',
            ]);

            $imported++;
        }
        session()->flash('flash', 'Nájdených ' . $count . ' kontaktov. Importovaných ' . $imported );

        return back();

    }
}
