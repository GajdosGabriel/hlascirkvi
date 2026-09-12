<?php

namespace App\Http\Requests;

use App\Rules\IsHuman;
use App\Support\HumanCheck;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessengerRequest extends FormRequest
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
        if (auth()->guest()) {
            return [
                // Nahradilo „Som človek 7 plus 3" — viď App\Support\HumanCheck.
                // Prihlásený človek kontrolu nepotrebuje, ten už raz účtom prešiel.
                HumanCheck::STAMP => ['required', new IsHuman],
                'body' => 'required|min:3',
            ];
        }

        return [
            'body' => 'required|min:3',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            HumanCheck::STAMP.'.required' => 'Formulár nie je kompletný, obnovte stránku a skúste to znova.',
            'body.required' => 'Správa musí obsahovať min. 3 znaky',
        ];
    }
}
