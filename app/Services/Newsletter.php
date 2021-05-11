<?php


namespace App\Services;

use App\Prayer;
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


    public function prayerFulfilledOrNotYet()
    {
        $prayers = Prayer::where('user_id', '!=', 1)
          ->whereDay('last_notification', Carbon::now()->subDays(2))
          ->whereNull('fulfilled_at')
          ->get();

        foreach ($prayers as $prayer) {
            Notification::send($prayer->user, new PrayerFulfilledOrNotYet($prayer));

            $prayer->update([
                'last_notification' => Carbon::now()
            ]);
        }
    }
}
