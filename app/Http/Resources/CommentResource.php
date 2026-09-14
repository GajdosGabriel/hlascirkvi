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
        // dd($this->comments()->user);
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'created_at_humans' => $this->created_at->diffForHumans(),
            'commentable_id' => $this->commentable_id,
            'parent_id' => $this->parent_id,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            // 'commentable_type' => $this->commentable_type,
            'post_slug' => $this->commentable->slug,
            'post_title' => $this->commentable->title,
            'source' => $this->resource->fromYoutube() ? 'youtube' : 'site',
            // Pri komentári z YouTube je používateľ len zástupný účet —
            // komponent z neho potrebuje iba id na overenie práv.
            'user' => $this->resource->fromYoutube() ? ['id' => $this->user_id] : $this->user,
            'user_name' => $this->user_name ? $this->user_name : "{$this->user->first_name} {$this->user->last_name}",
            'user_avatar' => $this->user_avatar ? $this->user_avatar : $this->user?->avatar,
            'favorites' => $this->favorites,
            'is_favorited' => $this->isFavorited,
            'favorites_count' => $this->favoritesCount,

            // `show` sa tu generovalo, hoci PostCommentController takú akciu
            // nikdy nemal. Podmienka pri `destroy` bola `auth() || ...` —
            // helper vracia inštanciu guardu, teda vždy true, takže sa odkaz
            // pridával aj neprihláseným.
            'url' => [
                'index'     =>  route('posts.comments.index', $this->commentable_id),
                'update'    =>  route('posts.comments.update', [$this->commentable_id, $this->id]),
                'store'     =>  route('posts.comments.store', [$this->commentable_id]),
                'destroy'   =>  $this->when(
                    auth()->check() && auth()->user()->can('delete', $this->resource),
                    fn () => route('posts.comments.destroy', [$this->commentable_id, $this->id])
                ),
            ],
        ];
    }
}
