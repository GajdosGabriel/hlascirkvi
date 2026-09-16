<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Tvar položky zvončeka. `data` zostáva v pôvodnom tvare (message, link,
 * logo), lebo ho napĺňajú jednotlivé notifikácie v App\Notifications.
 * Pribudol čas a stav prečítania — zvonček podľa nich vykresľuje „pred 2 hod."
 * a zvýraznenie neprečítaných.
 */
/** @mixin DatabaseNotification */
class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $data = (array) $this->data;

        return [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'data' => [
                'message' => $data['message'] ?? '',
                'link' => $data['link'] ?? null,
                'logo' => $data['logo'] ?? null,
            ],
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            // Locale sa nastavuje explicitne — Carbon inak preloží čas podľa
            // svojho globálneho nastavenia, nie podľa app.locale.
            'created_for_humans' => $this->created_at?->locale(app()->getLocale())->diffForHumans(),
        ];
    }
}
