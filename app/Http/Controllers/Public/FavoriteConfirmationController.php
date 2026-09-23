<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Canal;
use App\Models\PendingFavorite;
use App\Models\Post;
use App\Services\PendingConfirmation;
use Illuminate\Http\Request;

/**
 * Odkaz z e-mailu (App\Notifications\User\ConfirmFavorite). Až tu vzniká
 * účet — rovno overený, do `users` iný nepatrí — a čakajúce označenia
 * („Pripojiť sa k modlitbe", odber kanála) sa presunú do `favorites`.
 *
 * Kliknutím je adresa preukázaná, preto neprihláseného prihlásime. Toho, kto
 * je už prihlásený, na iný účet neprepíname.
 */
class FavoriteConfirmationController extends Controller
{
    public function __invoke(Request $request, string $token, PendingConfirmation $confirmation)
    {
        $pending = PendingFavorite::findByToken($token);

        if (! $pending) {
            return redirect()->route('modlitby.index')
                ->with('flash', 'Odkaz už bol použitý alebo mu vypršala platnosť.');
        }

        $model = $pending->favorited;

        if (! $user = $confirmation->confirm($pending)) {
            return redirect()->route('modlitby.index')
                ->with('flash', 'Účet s touto adresou nie je aktívny, preto to nemožno potvrdiť.');
        }

        if (! $request->user()) {
            auth()->login($user, true);
        }

        [$url, $message] = match (true) {
            $model instanceof Canal => [route('organizations.show', $model), 'Ďakujeme, adresa je potvrdená a kanál odoberáte.'],
            $model instanceof Post => [$model->path(), 'Ďakujeme, adresa je potvrdená.'],
            default => [route('modlitby.index'), 'Ďakujeme, adresa je potvrdená a ste pripojený k modlitbe.'],
        };

        return redirect($url)->with('flash', $message);
    }
}
