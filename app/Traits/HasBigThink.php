<?php

namespace App\Traits;

use App\Models\BigThink;


trait HasBigThink
{

    public function bigThinks()
    {
        return $this->hasMany(BigThink::class);
    }

    public function addBigThink($comment)
    {
        if (auth()->user()) {
            $idUser = auth()->user()->organizations()->wherePerson(1)->first()->id;
        } else {
            $idUser =  1;
        }
        return $this->bigThinks()->create(array_merge($comment, ['organization_id' => $idUser]));
    }
    
}