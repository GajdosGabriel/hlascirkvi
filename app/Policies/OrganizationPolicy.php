<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

   /**
     * Determine whether the user can view any organizations.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return mixed
     */
    public function view(User $user, Organization $organization)
    {
        return $user->org_id == $organization->id;
    }

    /**
     * Determine whether the user can create organizations.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return auth();
    }

    /**
     * Determine whether the user can update the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization   $organization
     * @return mixed
     */
    public function update(User $user, Organization $organization)
    {
        return $user->org_id == $organization->id;
    }

    /**
     * Správa kanála z profilu užívateľa (/user/{user}/organization/{organization}).
     *
     * Zoznam, z ktorého sa tam vchádza, stojí na väzbe $user->organizations()
     * (pivot organization_user), nie na org_id — užívateľ môže spravovať viac
     * kanálov, ale primárny má len jeden. `update` tu preto nestačí; tá gate-uje
     * príspevky a modlitby a jej význam nechávame nezmenený.
     *
     * @return bool
     */
    public function manage(User $user, Organization $organization)
    {
        return $user->org_id == $organization->id
            || $user->organizations()->whereKey($organization->getKey())->exists();
    }

    /**
     * Determine whether the user can delete the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization   $organization
     * @return mixed
     */
    public function delete(User $user, Organization $organization)
    {
        return $user->org_id == $organization->id;
    }

    /**
     * Determine whether the user can restore the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization   $organization
     * @return mixed
     */
    public function restore(User $user, Organization $organization)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization   $organization
     * @return mixed
     */
    public function forceDelete(User $user, Organization $organization)
    {
        //
    }
}
