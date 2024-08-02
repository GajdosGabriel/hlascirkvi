<?php

namespace App\Services\PostService;

use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use App\Services\Files\Form;



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

            $file = (new Form($post, $request))->handler();
            // $file->store();
        });
    }

    public function update($post, $request)
    {
        $post->update($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $file =  (new Form($post, $request))->handler();
        // $file->store();

        return $post;
    }
}
