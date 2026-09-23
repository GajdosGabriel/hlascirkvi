<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:17
 */

namespace App\Repositories\Contracts;


interface UserRepository extends InterfaceRepository
{

    public function createFromPendingRegistration(\App\Models\PendingRegistration $pending): \App\Models\User;
    public function createUserBySocial($value);
    public function usersHasRoleAdmin();

}