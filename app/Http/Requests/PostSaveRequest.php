<?php

namespace App\Http\Requests;

use App\Enums\PostSection;
use App\Services\Youtube\VideoId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Stĺpec posts.title je varchar(200); dlhší nadpis prešiel
            // validáciou a spadol až v databáze.
            'title' => 'required|string|max:200|min:3',
            // Príspevky z YouTube importu text nemajú (vyše 5000 kusov) — kým
            // bol text povinný vždy, nedali sa upraviť vôbec.
            'body' => 'nullable|required_without:video_id|string|min:3',
            // Pole vo formulári sa doteraz vo validácii nespomínalo, takže ho
            // validated() zahodilo a zmena odkazu na video sa neuložila.
            'video_id' => ['nullable', 'string', 'regex:' . VideoId::PATTERN],
            // Do ktorého výpisu príspevok patrí a či ide von hneď. Predtým
            // to bolo jedno pole `updaters` s id z číselníka, ktoré znamenalo
            // oboje naraz.
            'section' => ['required', Rule::enum(PostSection::class)],
            'publish_now' => 'nullable|boolean',

            /*
             * Výber kanála sa vo formulári ukáže len administrácii
             * (resources/views/posts/form.blade.php:48). Kým sa hodnota
             * nevalidovala a do modelu šla cez $request->all(), dal sa
             * príspevok doposlaním tohto poľa presunúť do cudzieho kanála.
             */
            'canal_id' => [
                'sometimes', 'integer', 'exists:canals,id',
                function ($attribute, $value, $fail) {
                    if (auth()->user()->can('superadmin')) {
                        return;
                    }

                    if (! auth()->user()->canals()->whereKey($value)->exists()) {
                        $fail('Do tohto kanála nemôžete publikovať.');
                    }
                },
            ],

            /*
             * accept="image/*" vo formulári je len nápoveda pre prehliadač,
             * nie kontrola. Bez týchto pravidiel skončil ľubovoľný súbor
             * v dekodéri obrázka a používateľ videl 500.
             */
            'pictures' => 'sometimes|array|max:20',
            'pictures.*' => 'file|image|mimes:jpg,jpeg,png,webp,gif|max:15360',
        ];
    }

    /**
     * Formulár žiada „odkaz na video", do stĺpca však patrí ID — náhľad sa
     * sťahuje z img.youtube.com/vi/{ID}. Odkaz preto prepíšeme na ID; čo sa
     * určiť nedá, ostane nezmenené a skončí hláškou pri poli.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('video_id')) {
            $video = trim((string) $this->input('video_id'));

            $this->merge([
                'video_id' => $video === '' ? null : (VideoId::fromInput($video) ?? $video),
            ]);
        }
    }

    public function messages()
    {
        return [
            'body.required' => 'Článok neobsahuje žiadny text.',
            'title.required' => 'Článok musí mať nadpis.',
            'title.min' => 'Minimálna dĺžka nadpisu sú 3 znaky.',
            'title.max' => 'Maximálna dĺžka nadpisu je 200 znakov.',
            'body.required_without' => 'Článok bez videa musí obsahovať text.',
            'video_id.regex' => 'Video sa nedalo určiť. Zadajte odkaz na video YouTube alebo jeho ID.',
            'pictures.max' => 'Naraz je možné pridať najviac 20 obrázkov.',
            'pictures.*.image' => 'Súbor :position nie je obrázok.',
            'pictures.*.mimes' => 'Povolené formáty sú JPG, PNG, WEBP a GIF.',
            'pictures.*.max' => 'Obrázok smie mať najviac 15 MB.',
        ];
    }
}
