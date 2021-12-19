<?php

namespace App\Policies;

use App\Models\Seminar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeminarPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any seminars.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the seminar.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function view(User $user, Seminar $seminar)
    {
        //
    }

    /**
     * Determine whether the user can create seminars.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the seminar.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function update(User $user, Seminar $seminar)
    {
        //
    }

    /**
     * Determine whether the user can delete the seminar.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function delete(User $user, Seminar $seminar)
    {
        //
    }

    /**
     * Determine whether the user can restore the seminar.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function restore(User $user, Seminar $seminar)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the seminar.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Seminar  $seminar
     * @return mixed
     */
    public function forceDelete(User $user, Seminar $seminar)
    {
        //
    }
}
