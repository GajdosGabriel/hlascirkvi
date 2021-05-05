<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    protected $posts;
    protected $events;
    protected $prayers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($posts, $events = null, $prayers = null)
    {
        $this->posts = $posts;
        $this->events = $events;
        $this->prayers = $prayers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.posts', ['posts' => $this->posts, 'events' => $this->events, 'prayers' => $this->prayers]);
    }
}
