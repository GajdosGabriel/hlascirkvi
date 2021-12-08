<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'body' => $this->body,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'comment_post' => $this->commentable,
            'user' => $this->user,
            'favorites' => $this->favorites,
            'is_favorited' => $this->isFavorited,
            'favorites_count' => $this->favoritesCount,
        ];
    }
}
