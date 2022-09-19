<?php

namespace App\Actions;

use App\Contracts\UpdatePostContract;
use App\Services\Form;

class UpdatePost implements UpdatePostContract
{

    public function handle($post, $request)
    {
        $post->update($request->except(['picture', 'updaters']));

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
