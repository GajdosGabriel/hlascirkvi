<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name .' '. $this->last_name,
            'verified' => $this->verified,
            'avatar' => $this->avatar,
            'gender' => $this->gender,
            'description' => $this->description,
            'canal' => new CanalResource($this->canal),
            'canal_id' => $this->canal_id,
            'notify_bell' => $this->notify_bell,
            // Zoznam notifikácií si zvonček dotiahne až pri otvorení
            // (GET /api/notifications) — tu stačí počet neprečítaných na
            // odznak. Predtým sa ku každému načítaniu užívateľa serializovalo
            // desať notifikácií, ktoré väčšinou nikto neotvoril.
            'countNotifycation' => $this->unreadNotifications()->count(),
            'isAdmin' => auth()->user()->hasRole(['admin']) ? true : false,
            'isSuperadmin' => $this->when( auth()->user()->hasRole(['superadmin']), true)
        ];

        // 'navigations' => [
        //     'show' =>  $this->when(auth()->user()->can("view", $this->resource), [
        //         'name' => 'Zobraziť',
        //         'title' => 'Zobraziť položku',
        //         'action' => 'show',
        //         'url' => route('organization.contact.show', [$this->canal_id, $this->id]),
        //         'icon' => 'iconShow',
        //     ]),

        //     'edit' =>  $this->when(auth()->user()->can("update", $this->resource), [
        //         'name' => 'Upraviť',
        //         'title' => 'Upraviť položku',
        //         'action' => 'edit',
        //         'url' => route('organization.contact.edit', [$this->canal_id, $this->id]),
        //         'typeOfButton' => 'button',
        //         'icon' => 'iconEdit',
        //     ]),

        //     'delete' => $this->when(auth()->user()->can("delete", $this->resource), [
        //         'name' => 'Zmazať',
        //         'title' => 'Zmazať položku',
        //         'action' => 'delete',
        //         'typeOfButton' => 'button',
        //         'url' => route('organizations.contacts.destroy', [$this->canal_id, $this->id]),
        //         'icon' => 'iconDelete',
        //     ])
        // ],

    }
}
