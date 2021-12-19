<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Prayer;
use App\Notifications\Prayer\NewPrayer;
use Illuminate\Support\Facades\Notification;


class PrayerObserver
{
    public function created(Prayer $prayer)
    {
        Notification::send(User::role('admin')->get(), new NewPrayer($prayer));
    }
}
