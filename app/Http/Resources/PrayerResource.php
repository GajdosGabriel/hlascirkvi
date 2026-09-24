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
            'canal_id' => $this->canal_id,
            // Hodnota musí byť uzáver, inak sa $this->canal načíta pri
            // každej modlitbe aj vtedy, keď ju when() nakoniec zahodí.
            'canal_title' => $this->when(
                auth()->check() && auth()->user()->hasRole('superadmin'),
                fn () => $this->canal->title
            ),
            'user_name' => $this->user_name,
            'body' => $this->body,
            'favoritesCount' => $this->favoritesCount,
            'isFavorited' => $this->isFavorited,
            'fulfilled_at' => $this->fulfilled_at,
            'published' => $this->published,
            'created_at' => $this->created_at,
            'created_at_humans' => $this->created_at->diffForHumans(),
        ];
    }
}
