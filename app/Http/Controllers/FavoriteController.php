<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Prayer;
use App\Models\Comment;
use App\Http\Requests\FavoriteRequest;
use App\Models\Canal;
use App\Repositories\Eloquent\EloquentUserRepository;

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
        'Organization' => Canal::class,
    ];

    public function __construct()
    {
        $this->middleware('auth')->except('update');
    }

    public function update(FavoriteRequest $request, $favorite)
    {
        if ($request->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($request);
        }

        $class = self::MODELS[$request->validated()['model']];

        $model = $class::find($request->validated()['model_id']);

        abort_if($model === null, 404);

        $model->favorite();

        if (request()->expectsJson()) return $model;

        return back();
    }

}
