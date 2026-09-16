<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Canal;
use Illuminate\Auth\Access\HandlesAuthorization;

class CanalPolicy
{
    use HandlesAuthorization;

   /**
     * Determine whether the user can view any canals.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the canal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Canal  $canal
     * @return mixed
     */
    public function view(User $user, Canal $canal)
    {
        return $user->canal_id == $canal->id;
    }

    /**
     * Determine whether the user can create canals.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return auth();
    }

    /**
     * Determine whether the user can update the canal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Canal   $canal
     * @return mixed
     */
    public function update(User $user, Canal $canal)
    {
        return $user->canal_id == $canal->id;
    }

    /**
     * Správa kanála z profilu užívateľa (/user/{user}/organization/{organization}).
     *
     * Zoznam, z ktorého sa tam vchádza, stojí na väzbe $user->canals()
     * (pivot canal_user), nie na canal_id — užívateľ môže spravovať viac
     * kanálov, ale primárny má len jeden. `update` tu preto nestačí; tá gate-uje
     * príspevky a modlitby a jej význam nechávame nezmenený.
     *
     * @return bool
     */
    public function manage(User $user, Canal $canal)
    {
        return $user->canal_id == $canal->id
            || $user->canals()->whereKey($canal->getKey())->exists();
    }

    /**
     * Determine whether the user can delete the canal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Canal   $canal
     * @return mixed
     */
    public function delete(User $user, Canal $canal)
    {
        return $user->canal_id == $canal->id;
    }

    /**
     * Determine whether the user can restore the canal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Canal   $canal
     * @return mixed
     */
    public function restore(User $user, Canal $canal)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the canal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Canal   $canal
     * @return mixed
     */
    public function forceDelete(User $user, Canal $canal)
    {
        //
    }
}
