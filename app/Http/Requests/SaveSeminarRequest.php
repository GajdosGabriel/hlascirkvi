<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Semináre boli jediná agenda kanála bez FormRequestu — `store` aj `update`
 * brali `$request->all()` na modeli s $guarded = ['id'], takže sa dalo poslať
 * aj `organization_id` a `published`.
 *
 * `published` posiela prepínač v resources/js/seminars/seminar-info.vue ako
 * timestamp, resp. prázdny reťazec pri vypnutí.
 */
class SaveSeminarRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        // Prepínač zverejnenia posiela cez PUT len `published`, takže pri
        // úprave sa validujú len tie polia, ktoré naozaj prišli.
        $title = $this->isMethod('POST') ? 'required' : 'sometimes|required';

        return [
            'title'            => $title . '|string|min:3|max:255',
            'description'      => 'nullable|string',
            'youtube_playlist' => 'nullable|string|max:255',
            'published'        => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Názov musí obsahovať aspoň tri znaky',
        ];
    }
}
