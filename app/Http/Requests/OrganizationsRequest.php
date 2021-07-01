<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationsRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255|min:3|unique:organizations,title',
            // 'street' => 'string',
           'phone' => 'nullable|digits:16',
           'village_id' => 'required|integer|exists:villages,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Názov musí obsahovať aspoň tri znaky',
            'title.unique' => 'Názov kanála už existuje. Ak si nárokujete názov kanála, kontaktujte administrátora.',
            // 'street.required' => 'Musí mať aspoň jeden znak',
            'phone' => 'Obsahuje veľa znakov. Limit je do 16 znakov',
        ];
    }

    public function save()
    {
        $organization = auth()->user()->organizations()->create($this->except(['updaters']));
        $organization->updaters()->sync($this->get('updaters'));
    }
}
