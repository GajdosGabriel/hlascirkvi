<?php

namespace App\Services\PostService;

use App\Services\Form;
use App\Models\Organization;
use App\Services\FileService\FileService;



class PostService {

    protected $fileService;


    public function __construct(FileService $fileService)
    {    
        $this->fileService = $fileService;        
    }

    public function store($organization, $request) 
    {
        $post = $organization->posts()->create($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $this->addImages($post, $request);
    }

    public function update($post, $request) 
    {
        $post->update($request->all());

        $post->updaters()->sync($request->get('updaters') ?: []);

        $this->addImages($post, $request);

        return $post;
    }

    private function addImages($post, $request)
    {
        (new Form($post, $request))->handler();
    }




  
}