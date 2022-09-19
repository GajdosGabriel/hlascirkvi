<?php



namespace App\Contracts;


interface UpdatePostContract {

    public function handle($post, $request);
}