<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Prayer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentEventRepository;

class PostNewsletter extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $posts   = (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get();
        $events  = (new EloquentEventRepository)->orderByStarting()->take(5)->get();
        $prayers = Prayer::latest()->take(5)->get();

        return $this->subject('Najlepšie kresťanské videa')->view('emails.posts', ['posts' => $posts, 'events' => $events, 'prayers' => $prayers]);
    }
}
