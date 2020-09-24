<?php

namespace App\Observers;

use App\User;
use Notification;
use App\FirstName;
use Illuminate\Support\Str;
use App\Notifications\User\NewRegistration;


class UserObserver
{

    public function saving(User $user)
    {
        $user->slug = Str::slug($user->first_name . $user->last_name, '-');

        $firstName = FirstName::whereName($user->first_name)->orderBy('count', 'desc')->first();

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
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $user->assignRole('user');

        $organization = $user->organizations()->create([
            'title' => $user->fullname,
            'slug' => $user->slug,
            'person' => 1,
            'region_id' => 9
        ]);

        $user->update(['org_id' => $organization->id]);


        //  Send notification new User registration to admin
        Notification::send(User::role('admin')->get(), new NewRegistration($user));
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
