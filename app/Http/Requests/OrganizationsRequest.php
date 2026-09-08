<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! auth()->check()) {
            return false;
        }

        // Pri úprave existujúceho kanála sa autorizuje ešte pred validáciou.
        // Inak by cudzí užívateľ dostal 422 a z chybových hlášok vyčítal,
        // aké polia formulár prijíma, hoci ku kanálu nemá prístup.
        $organization = $this->route('organization');

        return $organization === null
            || auth()->user()->can('manage', $organization);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Pri úprave musí unique pravidlo ignorovať samotný upravovaný kanál,
        // inak by sa nedalo uložiť nič bez zmeny názvu.
        $organization = $this->route('organization');

        return [
            'title' => [
                'required', 'string', 'max:255', 'min:3',
                Rule::unique('organizations', 'title')->ignore($organization),
            ],
            'description'      => 'nullable|string',
            'street'           => 'nullable|string|max:255',
            'phone'            => 'nullable|numeric',
            'email'            => 'nullable|email',
            'url_www'          => 'nullable|string|max:255',
            'mod_title'        => 'nullable|string|max:255',
            'village_id'       => 'required|integer|exists:villages,id',
            'youtube_channel'  => 'nullable|string|max:255',
            'youtube_playlist' => 'nullable|string|max:255',
            'updaters'         => 'nullable|array',
            'updaters.*'       => 'integer|exists:updaters,id',
            // `users` a `published` sa vykresľujú len v @can('superadmin') bloku
            // formulára (resources/views/organizations/edit.blade.php:82).
            // Kontrolu role robí controller, tu ide len o tvar dát.
            'users'            => 'nullable|array',
            'users.*'          => 'integer|exists:users,id',
            'published'        => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Názov musí obsahovať aspoň tri znaky',
            'title.unique' => 'Názov kanála už existuje. Ak si nárokujete názov kanála, kontaktujte administrátora.',
            'street' => 'maximálna dlžka je 255 znakov.',
            'phone' => 'Obsahuje veľa znakov. Limit je do 16 znakov',
        ];
    }

    public function save()
    {
        // Zakladá sa len z overených polí. `except()` prepúšťalo aj _token,
        // _method a čokoľvek iné, čo prišlo v tele požiadavky.
        $data = collect($this->validated())
            ->except(['updaters', 'users', 'published'])
            ->all();

        $organization = auth()->user()->organizations()->create($data);
        $organization->updaters()->sync($this->input('updaters', []));

        return $organization;
    }
}
