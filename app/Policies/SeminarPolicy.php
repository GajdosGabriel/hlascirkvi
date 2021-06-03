<?php

namespace App\Policies;

use App\User;
use App\Seminar;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeminarPolicy
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
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function update(User $user, Seminar $seminar)
    {
        return auth()->user()->org_id == $seminar->organization_id;
    }
}
