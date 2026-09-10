<?php



namespace App\Contracts;
use App\Models\Canal;

interface StorePostContract {

    public function handle(Canal $organization, $request);
}