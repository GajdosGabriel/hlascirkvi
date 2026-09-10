<?php

namespace App\Policies;

use App\Models\Seminar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Všetkých sedem metód tu malo prázdne telo, čiže vracali null = zamietnuť.
 * `authorize()` nad seminárom preto prechádzal len superadminovi cez
 * Gate::before — a práve preto `store` v CanalSeminarController
 * autorizáciu vôbec nemal, inak by zakladanie seminárov nefungovalo.
 *
 * Seminár patrí kanálu; vlastníctvo kanála je väzba $user->organizations()
 * rovnako ako v PostPolicy.
 */
class SeminarPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Seminar $seminar): bool
    {
        return $this->owns($user, $seminar);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Seminar $seminar): bool
    {
        return $this->owns($user, $seminar);
    }

    public function delete(User $user, Seminar $seminar): bool
    {
        return $this->owns($user, $seminar);
    }

    public function restore(User $user, Seminar $seminar): bool
    {
        return $this->owns($user, $seminar);
    }

    public function forceDelete(User $user, Seminar $seminar): bool
    {
        return false;
    }

    protected function owns(User $user, Seminar $seminar): bool
    {
        return $seminar->organization_id !== null
            && $user->organizations()->whereKey($seminar->organization_id)->exists();
    }
}
