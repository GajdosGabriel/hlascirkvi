<?php

namespace App\Services\PostService;

use Illuminate\Support\Facades\DB;
use App\Services\Files\Files;



class PostService
{

    protected $fileService;
    protected $request;


    public function __construct()
    {
        // $this->fileService = ;               
    }

    public function store($organization, $request)
    {
        DB::transaction(function () use ($organization, $request) {
            $post = $organization->posts()->create($request->all());

            $post->updaters()->sync($request->get('updaters') ?: []);

            $file = new Files($post, $request);
            $file->store();
        });
    }

    public function update($post, $request)
    {
        $post->update($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $file = new Files($post, $request);
        $file->store();

        return $post;
    }
}
