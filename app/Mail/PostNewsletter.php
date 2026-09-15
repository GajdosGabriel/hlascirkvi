<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * Mesačný newsletter. Beží na rovnakej téme ako notifikácie
 * (x-mail::message), predtým mal vlastné HTML so sivým pruhom navrchu.
 *
 * Zásady ochrany osobných údajov sľubujú odkaz na odhlásenie v každom
 * e-maile — nebol v žiadnom. Je v pätičke aj v hlavičke List-Unsubscribe,
 * podľa ktorej Gmail a spol. zobrazia vlastné tlačidlo „Zrušiť odber".
 */
class PostNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Obsah prichádza zvonku (App\Services\Newsletter). Predtým si ho build()
     * doťahoval sám, čiže dvoma dopytmi na každého jedného príjemcu.
     */
    public function __construct(
        public Collection $posts,
        public Collection $prayers,
        public User $recipient,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Novinky z HlasCirkvi.sk');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter',
            with: ['unsubscribeUrl' => $this->unsubscribeUrl()],
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>']);
    }

    /** Bez expirácie — odkaz musí fungovať aj v starom e-maile. */
    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['user' => $this->recipient->getKey()]);
    }
}
