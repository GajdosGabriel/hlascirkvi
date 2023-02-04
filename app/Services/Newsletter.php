<?php


namespace App\Services;

use App\Models\Prayer;
use App\Mail\PostNewsletter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\PrayerFulfilledOrNotYet;
use App\Repositories\Eloquent\EloquentUserRepository;
use Carbon\Carbon;

class Newsletter
{

    public function mountlyNewsletter()
    {
        $users = (new EloquentUserRepository)->usersEmailable()->get();
        // $users = (new EloquentUserRepository)->usersHasRoleAdmin();

        $this->handle($users);
    }

    public function handle($users)
    {
        foreach ($users as $user) {
            Mail::to($user)->send(new PostNewsletter());
        }
    }

    // Notifikácie vypočutia modlitieb
    public function prayerFulfilledOrNotYet()
    {
        // Počet dní po ktorých sa posiela dotaz či modlitba bola vypočutá.
        $days = array(20, 70, 90, 180);

        foreach ($days as $day) {
            $prayers =  $this->prayerFulfilledAfterDays($day);
            $this->prayerFulfilledHandle($prayers);
        }
    }

    public function prayerFulfilledAfterDays($day)
    {
        return Prayer::where('organization_id', '!=',[100, 648, 649, 650])
            ->whereDate('created_at', Carbon::now()->subDays($day))
            ->whereNull('fulfilled_at')
            ->get();
    }


    public function prayerFulfilledHandle($prayers)
    {
        foreach ($prayers as $prayer) {
            Notification::send($prayer->organization->user, new PrayerFulfilledOrNotYet($prayer));
        }
    }
        // End of Notifikácie vypočutia modlitieb
}
