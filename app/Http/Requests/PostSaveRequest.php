<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostSaveRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
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

            /*
             * accept="image/*" vo formulári je len nápoveda pre prehliadač,
             * nie kontrola. Bez týchto pravidiel skončil ľubovoľný súbor
             * v dekodéri obrázka a používateľ videl 500.
             */
            'pictures' => 'sometimes|array|max:20',
            'pictures.*' => 'file|image|mimes:jpg,jpeg,png,webp,gif|max:15360',
        ];
    }

    public function messages()
    {
        return [
            'body.required' => 'Článok neobsahuje žiadny text.',
            'title.required' => 'Článok musí mať nadpist.',
            'title.min' => 'Minimálna dľžka nadpisu sú 3 znaky.',
            'title.max' => 'Maximálna dľžka nadpisu je 255 znakov.',
            'pictures.max' => 'Naraz je možné pridať najviac 20 obrázkov.',
            'pictures.*.image' => 'Súbor :position nie je obrázok.',
            'pictures.*.mimes' => 'Povolené formáty sú JPG, PNG, WEBP a GIF.',
            'pictures.*.max' => 'Obrázok smie mať najviac 15 MB.',
        ];
    }
}
