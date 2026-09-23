<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Prayer;
use App\Models\Comment;
use App\Http\Requests\FavoriteRequest;
use App\Models\Canal;
use App\Services\PendingConfirmation;

class FavoriteController extends Controller
{
    /**
     * Modely, ktoré sa dajú označiť ako obľúbené (trait App\Traits\HasFavorites).
     * Pôvodne sa trieda skladala reťazcom z requestu — "App\Models\{$model}" —
     * a rovno inštanciovala, takže vstup rozhodoval o tom, čo sa vytvorí.
     */
    public const MODELS = [
        'Post'         => Post::class,
        'Prayer'       => Prayer::class,
        'Comment'      => Comment::class,
        'Canal'        => Canal::class,
    ];

    public function __construct()
    {
        $this->middleware('auth')->except('update');
    }

    public function update(FavoriteRequest $request, $favorite, PendingConfirmation $confirmation)
    {
        $class = self::MODELS[$request->validated()['model']];

        $model = $class::find($request->validated()['model_id']);

        abort_if($model === null, 404);

        // Neprihlásený: do `users` sa nezapisuje nič, pripojenie čaká na
        // potvrdenie e-mailu (Public\FavoriteConfirmationController).
        if (auth()->guest()) {
            $confirmation->queueFavorite($model, $request->validated()['email'], $request);

            if ($request->expectsJson()) {
                return response()->json(['pending' => true], 202);
            }

            return back()->with('flash', 'Poslali sme vám e-mail — pripojenie sa započíta po jeho potvrdení.');
        }

        $model->favorite();

        if (request()->expectsJson()) return $model;

        return back();
    }

}
