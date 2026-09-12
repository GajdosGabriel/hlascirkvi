<?php

namespace App\Http\Requests;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    /**
     * Oznamy sú výhradne superadminská agenda. Routy už stoja za middleware
     * `checkSuperAdmin`, kontrola tu je poistka pre prípad, že by sa formulár
     * raz volal odinakiaľ.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            'placement'       => ['required', Rule::in(AnnouncementPlacement::values())],
            'variant'         => ['required', Rule::in(AnnouncementVariant::values())],
            'title'           => ['required', 'string', 'max:191'],
            'body'            => ['nullable', 'string', 'max:2000'],
            // Odkaz smie viesť len na http(s); `javascript:` v atribúte href
            // by inak spravil z oznamu spúšťač skriptu.
            'link_url'        => ['nullable', 'url:http,https', 'max:191'],
            'link_text'       => ['nullable', 'string', 'max:60'],
            'dismissible'     => ['nullable', 'boolean'],
            'active'          => ['nullable', 'boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0', 'max:65535'],
            'published_from'  => ['nullable', 'date'],
            'published_until' => ['nullable', 'date', 'after_or_equal:published_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                 => 'Oznam musí mať názov.',
            'link_url.url'                   => 'Odkaz musí byť úplná adresa vrátane http:// alebo https://.',
            'published_until.after_or_equal' => 'Koniec zobrazovania nemôže byť skôr ako jeho začiatok.',
        ];
    }

    /**
     * Zaškrtávacie polia formulár pri vypnutí neposiela vôbec a prázdne
     * dátumy prídu ako "". Bez tohto by sa oznam nedal vypnúť a prázdne okno
     * by spadlo na validácii dátumu.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'active'          => $this->boolean('active'),
            'dismissible'     => $this->boolean('dismissible'),
            'sort_order'      => $this->input('sort_order') === '' ? 0 : $this->input('sort_order'),
            'published_from'  => $this->input('published_from') ?: null,
            'published_until' => $this->input('published_until') ?: null,
            'link_url'        => $this->input('link_url') ?: null,
            'link_text'       => $this->input('link_text') ?: null,
            'body'            => $this->input('body') ?: null,
        ]);
    }

    /** Hodnoty pripravené na zápis do modelu. */
    public function payload(): array
    {
        $data = $this->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
