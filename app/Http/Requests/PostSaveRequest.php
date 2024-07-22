<?php

namespace App\Http\Requests;

use App\Models\Image;
use App\Models\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    public function __construct()
    {
        //
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'body' => 'required|string|min:3',
            'updaters' => 'required|integer|exists:updaters,id',
            // 'organization_id' => 'required|integer|exists:organizations,id',
        ];
    }

    public function messages()
    {
        return [
            'body.required' => 'Článok neobsahuje žiadny text.',
            'title.required' => 'Článok musí mať nadpist.',
            'title.min' => 'Minimálna dľžka nadpisu sú 3 znaky.',
            'title.max' => 'Maximálna dľžka nadpisu je 255 znakov.',
        ];
    }
}
