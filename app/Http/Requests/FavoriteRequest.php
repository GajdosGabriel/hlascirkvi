<?php

namespace App\Http\Requests;

use App\Http\Controllers\FavoriteController;
use App\Rules\IsHuman;
use App\Support\HumanCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Hodnota sa v controlleri používa na výber modelu, takže musí byť
            // z uzavretého zoznamu. Kým bola len `string`, dala sa cez ňu
            // inštanciovať ľubovoľná trieda z App\Models.
            'model' => ['required', 'string', Rule::in(array_keys(FavoriteController::MODELS))],
            'model_id' => 'integer|required',
            // Neprihlásený sa pripája cez e-mail (App\Models\PendingFavorite).
            'email' => [Rule::requiredIf(auth()->guest()), 'nullable', 'email', 'max:100'],
            // Prihlásený posiela cieľový stav, nie „prepni".
            'favorited' => ['sometimes', 'boolean'],
            // Neprihlásený spúšťa odoslanie e-mailu na ľubovoľnú adresu.
            HumanCheck::STAMP => [Rule::requiredIf(auth()->guest()), new IsHuman],
        ];
    }
}
