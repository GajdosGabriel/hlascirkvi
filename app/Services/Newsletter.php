<?php


namespace App\Services;


use App\Mail\PostNewsletter;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Eloquent\EloquentUserRepository;

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
}
