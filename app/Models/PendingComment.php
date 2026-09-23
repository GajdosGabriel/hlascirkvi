<?php

namespace App\Models;

use App\Models\Concerns\ConfirmableByEmail;
use App\Notifications\Comments\ConfirmComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

/**
 * Komentár, ktorého autor ešte nepotvrdil e-mail.
 *
 * Kým adresa nie je potvrdená, v `users` ani `comments` nie je nič. Po
 * kliknutí na odkaz Public\CommentConfirmationController založí (alebo overí)
 * účet a komentár zverejní.
 */
class PendingComment extends Model
{
    use ConfirmableByEmail;

    protected $guarded = ['id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    protected function confirmationNotification(string $token, bool $reminder): Notification
    {
        return new ConfirmComment($token, $this->expires_at, $reminder);
    }
}
