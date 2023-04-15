<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BigThingsRequest extends FormRequest
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
                'body' => 'bail|required|min:3',
                'iamHuman' => 'required|in:10',
            ];
        }

        return [
            'body' => 'bail|required|min:3'
        ];
    }
}
