<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 18.09.2018
 * Time: 14:43
 */

namespace App\Services;

use Image;

class ImageResize
{
    protected $post;
    protected $videoPath;

    public function resizeImage($post, $videoPath) {

        \Storage::disk('public')->makeDirectory($this->folder($post));
        \Storage::disk('public')->makeDirectory($this->folder($post) . '/thumb');


        $file_name = $post->slug . '-' . rand(1000, 90000) . '.jpg';

        $img = Image::make($videoPath);

        $img->widen(360)
            ->save(storage_path('app/public/' . $this->folder($post)  . $file_name));

        $img->widen(195)
            ->save(storage_path('app/public/' . $this->folder($post) .'thumb/'  . $file_name));

        $post->images()->create([
            'url' => $this->folder($post) . $file_name,
            'thumb' => $this->folder($post). 'thumb/' . $file_name,
            'org_name' => $post->organization->title,
            'name' => $post->title,
            'mime' => 'jpg'
        ]);

    }

    protected function folder($post) {
        return strtolower(class_basename($post)).'s/' . $post->organization_id . '/';
    }

}