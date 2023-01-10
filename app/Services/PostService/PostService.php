<?php

namespace App\Services\PostService;

use App\Services\FileService\FileService;



class PostService {

    protected $fileService;
    protected $request;


    public function __construct()
    {    
        $this->fileService = new FileService();               
    }

    public function store($organization, $request) 
    {
        $post = $organization->posts()->create($request->all());

        $post->updaters()->sync($this->request->get('updaters') ?: []);

        $this->fileService->store($post, $request);
    }

    public function update($post, $request) 
    {
        $post->update($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $this->fileService->store($post, $request);

        return $post;
    }
  
}