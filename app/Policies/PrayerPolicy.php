<?php

namespace App\Policies;

use App\User;
use App\Prayer;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\User  $user
     * @param  \App\Prayer  $prayer
     * @return mixed
     */
    public function update(User $user, Prayer $prayer)
    {
        return auth()->user()->id == $prayer->user_id;
    }
}
