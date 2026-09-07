<?php

namespace App\Http\Requests;

use App\Http\Controllers\FavoriteController;
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
        ];
    }
}
