<?php

namespace App\Policies;

use App\Models\Prayer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any prayers.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the prayer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prayer  $prayer
     * @return mixed
     */
    public function view(User $user, Prayer $prayer)
    {
        return $this->owns($user, $prayer);
    }

    /**
     * Determine whether the user can create prayers.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user;
    }

    /**
     * Determine whether the user can update the prayer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prayer  $prayer
     * @return mixed
     */
    public function update(User $user, Prayer $prayer)
    {
        return $this->owns($user, $prayer);
    }

    /**
     * Determine whether the user can delete the prayer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prayer  $prayer
     * @return mixed
     */
    public function delete(User $user, Prayer $prayer)
    {
        return $this->owns($user, $prayer);
    }

    /**
     * Determine whether the user can restore the prayer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prayer  $prayer
     * @return mixed
     */
    public function restore(User $user, Prayer $prayer)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the prayer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prayer  $prayer
     * @return mixed
     */
    public function forceDelete(User $user, Prayer $prayer)
    {
        //
    }

    // Rozhoduje zoznam správcov kanála (rovnako ako CanalPolicy::manage),
    // nie aktívny kanál — správca viacerých kanálov upraví modlitbu v každom.
    protected function owns(User $user, Prayer $prayer): bool
    {
        return $prayer->canal_id !== null
            && $user->canals()->whereKey($prayer->canal_id)->exists();
    }
}
