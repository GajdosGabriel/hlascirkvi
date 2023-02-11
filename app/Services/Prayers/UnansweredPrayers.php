<?php

namespace App\Services\Prayers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\PrayerFulfilledOrNotYet;
use App\Repositories\Eloquent\EloquentPrayerRepository;


class UnansweredPrayers
{

    protected $prayers;

    // Počet dní po ktorých sa posiela dotaz či modlitba bola vypočutá.
    protected $days = array(20, 70, 90, 180);


    public function __construct()
    {
        $this->prayers = new EloquentPrayerRepository;
    }



    public function prayersForAsking()
    {
        foreach ($this->days as $day) {
            $prayers = $this->prayers->unansweredPrayers()->whereDate('created_at', Carbon::now()->subDays($day))->get();
            $this->prayerFulfilledHandle($prayers);
        }
    }

    public function prayerFulfilledHandle($prayers)
    {
        foreach ($prayers as $prayer) {
            Notification::send($prayer->organization->user, new PrayerFulfilledOrNotYet($prayer));
        }
    }
}
