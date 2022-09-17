<?php

namespace App\Http\Resources;

use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'organization_id' => $this->organization_id,
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
            'organization' => new OrganizationResource($this->organization),
            'hasUpdater' => $this->hasUpdater
        ];
    }
}
