<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PendingComment;
use App\Services\PendingConfirmation;
use Illuminate\Http\Request;

/**
 * Odkaz z e-mailu (App\Notifications\Comments\ConfirmComment). Až tu vzniká
 * účet — rovno overený, do `users` iný nepatrí — a čakajúce komentáre
 * s touto adresou sa presunú do `comments` a zverejnia.
 *
 * Kliknutím je adresa preukázaná, preto neprihláseného prihlásime. Toho, kto
 * je už prihlásený, na iný účet neprepíname.
 */
class CommentConfirmationController extends Controller
{
    public function __invoke(Request $request, string $token, PendingConfirmation $confirmation)
    {
        $pending = PendingComment::findByToken($token);

        if (! $pending) {
            return redirect()->route('posts.index')
                ->with('flash', 'Odkaz už bol použitý alebo mu vypršala platnosť.');
        }

        $post = $pending->post;

        if (! $user = $confirmation->confirm($pending)) {
            return redirect()->route('posts.index')
                ->with('flash', 'Účet s touto adresou nie je aktívny, komentár preto nemožno zverejniť.');
        }

        if (! $request->user()) {
            auth()->login($user, true);
        }

        return redirect($post ? $post->path() : route('posts.index'))
            ->with('flash', 'Ďakujeme, adresa je potvrdená. Komentáre, ktoré prešli kontrolou obsahu, sú zverejnené.');
    }
}
