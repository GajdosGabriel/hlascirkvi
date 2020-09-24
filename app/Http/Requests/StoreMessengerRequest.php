<?php

namespace App\Http\Requests;

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
        if(auth()->guest()) {
            return [
                // Vypnuté preto aby user-card mohol cez ajax zasielať správy
                'iamHuman' => 'required|in:5',
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
            'iamHuman.required' => 'Číslo musí byť 5',
            'body.required'  => 'Správa musí obsahovať min. 3 znaky',
        ];
    }
}
