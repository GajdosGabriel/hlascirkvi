<?php

namespace App\Services\PostService;

use App\Services\FileService\FileService;



class PostService {

    protected $fileService;
    protected $request;


    public function __construct()
    {    
        // $this->fileService = ;               
    }

    public function store($organization, $request) 
    {
        $post = $organization->posts()->create($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $file = new FileService($post, $request);
        $file->store();
    }

    public function update($post, $request) 
    {
        $post->update($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $file = new FileService($post, $request);
        $file->store();

        return $post;
    }
  
}