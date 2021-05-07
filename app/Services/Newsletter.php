<?php


namespace App\Services;

use App\Prayer;
use App\Mail\PostNewsletter;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentEventRepository;


class Newsletter
{

    public function mountlyNewsletter()
    {
        //   $users = (new EloquentUserRepository)->usersEmailable()->get();
        $users = (new EloquentUserRepository)->usersHasRoleAdmin();

       $this->handle($users);
    }

    public function handle($users)
    {

        $posts   = (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get();
        $events  = (new EloquentEventRepository)->firstStartingEvents()->take(5)->get();
        $prayers = Prayer::latest()->take(5)->get();

        foreach ($users as $user) {
            Mail::to($user)->send(new PostNewsletter($posts, $events, $prayers));
        }
    }
}
