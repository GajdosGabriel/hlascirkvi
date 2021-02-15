<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePrayerRequest extends FormRequest
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
        if(auth()->guest() ) {
            return [
                'title' => 'bail|required|min:2',
                'body' => 'bail|required|min:3',
                'user_name' => 'bail|required|min:2',
                'email' => 'required|email|max:255',
            ];
        }

        return [
            'body' => 'bail|required|min:3'
        ];
    }
}
