<?php

namespace App\Services\PostService;

use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use App\Services\Files\Form;



class PostService
{

    public function store($organization, $request)
    {
        DB::transaction(function () use ($organization, $request) {
            // validated() namiesto all() — do modelu sa tak nedostane nič, čo
            // PostSaveRequest nepovolil (published, count_view, cudzie
            // organization_id).
            $post = $organization->posts()->create($request->validated());

            $post->updaters()->sync($request->get('updaters') ?: []);

            $file = (new Form($post, $request))->handler();
            // $file->store();
        });
    }

    public function update($post, $request)
    {
        $post->update($request->validated());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $file =  (new Form($post, $request))->handler();
        // $file->store();

        return $post;
    }
}
