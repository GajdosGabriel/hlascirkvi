<?php

namespace App\Console\Commands;

use App\Post;
use App\User;
use App\Event;
use App\Prayer;
use Carbon\Carbon;
use App\Mail\PostNewsletter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentEventRepository;


class PostNewslleter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PostMonthlyReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send posts monthly report to all users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $posts      =  (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get();
        $events     = (new EloquentEventRepository)->firstStartingEvents()->take(5)->get();
        $prayers    = Prayer::latest()->take(5)->get();

        Mail::to(User::first())->send(new PostNewsletter($posts, $events, $prayers));
    }
}
