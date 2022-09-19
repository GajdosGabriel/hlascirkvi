<?php



namespace App\Contracts;
use App\Models\Organization;

interface StorePostContract {

    public function storePost(Organization $organization, $request);
}