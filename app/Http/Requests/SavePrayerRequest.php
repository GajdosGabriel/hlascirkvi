<?php

namespace App\Http\Requests;

use App\Rules\IsHuman;
use App\Rules\NoUrlLinkRule;
use App\Support\HumanCheck;
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
     * Názvy polí v chybách zodpovedajú popiskom vo formulári.
     */
    public function attributes()
    {
        return [
            'title' => 'Modlitba za',
            'body' => 'Viac o úmysle',
            'user_name' => 'Prezývka',
            'email' => 'E-mailová adresa',
        ];
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
                HumanCheck::STAMP => ['required', new IsHuman],
                'title' => [ 'required','min:3','max:255', new NoUrlLinkRule],
                'body' => [ 'required','min:3','max:3000', new NoUrlLinkRule],
                // Prezývka je nepovinná; bez nej sa prosba vypíše ako „Anonym".
                'user_name' => 'nullable|string|min:2|max:255',
                'email' => 'required|email|max:255',
            ];
        }

        // Zákaz odkazov chráni verejný formulár pred spamom. Správca kanála,
        // ktorý modlitbu zadáva v nástenke (/dashboard/canals/{canal}/prayers),
        // odkaz uviesť smie. Verejné API (/api/prayers) sa sem nepočíta — každý
        // užívateľ má vlastný automaticky založený kanál a obišiel by tak zákaz.
        $canal = $this->route('canal');
        $links = $canal && $this->user()->can('manage', $canal) ? [] : [new NoUrlLinkRule];

        // Vyše 3000 starších modlitieb nadpis nemá (text áno) a vypisujú sa ako
        // „Prosba o modlitbu". Pri ich úprave sa nadpis nevyžaduje, inak by
        // nešli uložiť bez vymýšľania nadpisu.
        $prayer = $this->route('prayer');
        $title = $prayer && blank($prayer->title) ? ['nullable', 'string', 'min:3', 'max:255'] : ['required', 'min:3', 'max:255'];

        // 'body' tu bolo dvakrát — druhý zápis prvý ticho prepísal.
        return [
            'title' => [ ...$title, ...$links],
            'body' => [ 'bail', 'required','min:3','max:3000', ...$links],
            'user_name' => 'nullable|string|min:2|max:255',
            'published' => $prayer && $this->user()->can('superadmin')
                ? ['sometimes', 'nullable', 'date']
                : ['exclude'],
        ];
    }
}
