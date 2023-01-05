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
            'created_at_humans' => $this->created_at->diffForHumans(),
            'commentable_id' => $this->commentable_id,
            // 'commentable_type' => $this->commentable_type,
            'post_slug' => $this->commentable->slug,
            'post_title' => $this->commentable->title,
            'user' => $this->user,
            'user_name' => $this->user_name ? $this->user_name : "{$this->user->first_name} {$this->user->last_name}",
            'user_avatar' => $this->user_avatar ? $this->user_avatar : $this->user->avatar,
            'favorites' => $this->favorites,
            'is_favorited' => $this->isFavorited,
            'favorites_count' => $this->favoritesCount,

            'url' => [
                'index'     =>  route('posts.comments.index', $this->commentable_id),
                'show'      =>  route('posts.comments.show', [$this->commentable_id, $this->id]),
                'update'    =>  route('posts.comments.update', [$this->commentable_id, $this->id]),
                'store'     =>  route('posts.comments.store', [$this->commentable_id]),
                'destroy' => $this->when(auth() || auth()->user()->can("delete", $this->resource), route('posts.comments.destroy', [$this->commentable_id, $this->id]))
            ],
        ];
    }
}
