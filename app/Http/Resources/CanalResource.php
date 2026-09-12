<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CanalResource extends JsonResource
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
            // Preklep 'avatgar' — v API tak bol avatar kanála vždy null.
            'avatar' => $this->avatar,
            'title' => $this->title,
            'street' => $this->street,
            'psc' => $this->psc,
            'description' => $this->description,
            'phone' => $this->phone,
            'youtube_channel' => $this->youtube_channel,
            'youtube_playlist' => $this->youtube_playlist,
            'url_www' => $this->url_www,
            'published' => $this->published,
            // Do 9/2026 tu bolo pole `updaters` — jedna spojovacia tabuľka
            // pre vierovyznanie, deň importu aj zaradenie do zoznamov naraz.
            'denomination' => $this->denomination?->value,
            'post_section' => $this->post_section?->value,
            'import_day' => $this->import_day,
        ];
    }
}
