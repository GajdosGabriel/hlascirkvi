<?php



namespace App\Contracts;
use App\Models\Organization;

interface StorePostContract {

    public function handle(Organization $organization, $request);
}