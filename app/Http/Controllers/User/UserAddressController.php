<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index(User $user)
    {
        $this->authorizeUser($user);

        $users = $user->addresBooks()->latest()->paginate(50);
        return view('profiles.import_contacts', ['user' => $user, 'users' => $users]);
    }


    public function store(User $user, Request $request) {

        $this->authorizeUser($user);

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

    /**
     * Adresár je zoznam cudzích e-mailových adries. Routa je pod /user/{user},
     * ale {user} sa neoveroval, takže si ho ktokoľvek prihlásený vedel vypísať
     * pre ľubovoľné ID.
     */
    protected function authorizeUser(User $user): void
    {
        abort_unless(auth()->id() === $user->id, 403);
    }
}
