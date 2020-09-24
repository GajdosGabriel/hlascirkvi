<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class SaveCommentsRequest extends FormRequest
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
//                'email' => 'required|email|max:255',
            ];
        }

        return [
            'body' => 'bail|required|min:3'
        ];

    }

    public function save($post)
    {
       return $post->addComment($this->only('body'));
    }


}
