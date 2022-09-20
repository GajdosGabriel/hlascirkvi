<?php

namespace App\Actions;

use App\Contracts\AddUpdaterContract;
use App\Services\Form;

class AddUpdater implements AddUpdaterContract
{

    public static function make($post, $updater_id)
    {
        $post->updaters()->sync($updater_id ?: []);
    }

}
