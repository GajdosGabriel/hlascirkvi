<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'village_id' => $this->village_id,
            'person' => $this->person,
            'avatar' => $this->avatgar,
            'title' => $this->title,
            'street' => $this->street,
            'psc' => $this->psc,
            'description' => $this->description,
            'phone' => $this->phone,
            'youtube_channel' => $this->youtube_channel,
            'youtube_playlist' => $this->youtube_playlist,
            'url_www' => $this->url_www,
            'published' => $this->published,
            'updaters' => $this->updaters,
        ];
    }
}
