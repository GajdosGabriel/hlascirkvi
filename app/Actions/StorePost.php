<?php

namespace App\Actions;

use App\Contracts\StorePostContract;
use App\Models\Organization;
use App\Services\Form;

class StorePost implements StorePostContract
{

    public function storePost(Organization $organization, $request)
    {
        $post = $organization->posts()->create($request->except(['picture', 'updaters']));

        $this->addUpdater($post, $request);
        $this->addImages($post, $request);
        return $post;
    }

    private function addUpdater($post, $request)
    {
        $post->updaters()->sync($request->get('updaters') ?: []);
    }

    private function addImages($post, $request)
    {
        (new Form($post, $request))->handler();
    }
}
