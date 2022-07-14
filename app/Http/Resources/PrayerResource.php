<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrayerResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'organization_title' => $this->when( auth()->user()->hasRole(['superadmin']), $this->organization->title),
            'user_name' => $this->user_name,
            'body' => $this->body,
            'favoritesCount' => $this->favoritesCount,
            'isFavorited' => $this->isFavorited,
            'fulfilled_at' => $this->fulfilled_at,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
