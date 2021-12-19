<?php

namespace App\Observers;

use App\Models\User;
use Notification;
use App\Models\FirstName;
use Illuminate\Support\Str;
use App\Notifications\User\NewRegistration;
use Carbon\Carbon;

class UserObserver
{

    public function saving(User $user)
    {
        $user->slug =  Str::slug($user->first_name . " " . $user->last_name, '-');

        // Ak zamenia first name s last name
        $firstName = FirstName::whereName($user->first_name)->orderBy('count', 'desc')->first();

        if(!$firstName){
            $firstName = FirstName::whereName($user->last_name)->orderBy('count', 'desc')->first();
        }

        $user->api_token = bin2hex(openssl_random_pseudo_bytes(30));

        if ($firstName) {
            $user->vocative = $firstName->vocative;
            $user->gender = $firstName->gender;
        } else {
            $user->vocative = null;
        }
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $user->assignRole('user');

        $organization = $user->organizations()->create([
            'title' => $user->fullname,
            'slug' => $user->slug,
            'person' => 1,
            'village_id' => 4209
        ]);

        $user->update([
            'org_id' => $organization->id
        ]);


        //  Send notification new User registration to admin
        Notification::send(User::role('admin')->get(), new NewRegistration($user));
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
