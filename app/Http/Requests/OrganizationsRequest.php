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
        return true;
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
            // 'street' => 'string',
            // 'city' => 'string',
//            'phone' => 'nullable|digits:16',
            'region_id' => 'required|integer|exists:regions,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Musí mať aspoň tri znaky',
            // 'street.required' => 'Musí mať aspoň jeden znak',
            // 'city.required' => 'Musí mať aspoň dva znaky',
            'phone' => 'obsahuje veľa znakov. Limit je do 16 znakov',
        ];
    }

    public function save()
    {
       $organization = auth()->user()->organizations()->create($this->except(['updaters']));
       $organization->updaters()->sync($this->get('updaters'));
    }
}
