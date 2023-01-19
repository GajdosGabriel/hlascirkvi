<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventSubscribeGuestForm extends FormRequest
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
        if(auth()->user()) return[];

        return [
            'first_name.*' => 'required|min:3',
            'last_name.*' => 'required|min:3',
            'email.*' => 'required|email|max:255',
            'iamHuman' => 'required|in:5',
        ];
    }


    public function messages()
    {
        return [
            'first_name.required' => 'musí mať aspoň tri znaky.',
            'last_name.required' => 'musí mať aspoň tri znaky.',
        ];
    }
}
