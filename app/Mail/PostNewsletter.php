<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PostNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $posts;
    public Collection $prayers;

    /**
     * Obsah prichádza zvonku (App\Services\Newsletter). Predtým si ho build()
     * doťahoval sám, čiže dvoma dopytmi na každého jedného príjemcu.
     */
    public function __construct(Collection $posts, Collection $prayers)
    {
        $this->posts = $posts;
        $this->prayers = $prayers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Najlepšie kresťanské videa')
            ->view('emails.posts', ['posts' => $this->posts, 'prayers' => $this->prayers]);
    }
}
