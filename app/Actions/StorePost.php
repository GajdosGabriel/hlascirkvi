<?php

namespace App\Actions;

use App\Contracts\AddUpdaterContract;
use App\Contracts\StorePostContract;
use App\Models\Organization;
use App\Services\Form;

class StorePost implements StorePostContract
{

    public function handle(Organization $organization, $request)
    {
        
        $post = $organization->posts()->create($request->except(['picture', 'updaters']));

        AddUpdater::make($post, $request->get('updaters'));

        $this->addImages($post, $request);

        return $post;
    }

    private function addImages($post, $request)
    {
        (new Form($post, $request))->handler();
    }
}
