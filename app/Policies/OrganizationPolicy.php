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
