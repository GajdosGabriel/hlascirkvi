<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'slug' => $this->slug,
            'video_id' => $this->video_id,
            'count_view' => $this->count_view,
            'url' => $this->url,
            'favoritesCount' => $this->favoritesCount,
            'isFavorited' => $this->isFavorited,
            'created_at' => $this->created_at,
            'createdAtHuman' => $this->createdAtHuman,
            'thumbImage' => $this->thumbImage,
//            'images' => Image::collection($this->images),
            'organization' => new Organization($this->organization)
        ];
    }
}
