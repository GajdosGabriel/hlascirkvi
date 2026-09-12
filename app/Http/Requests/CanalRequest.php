<?php

namespace App\Http\Requests;

use App\Enums\CanalSection;
use App\Enums\Denomination;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\PlaylistId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CanalRequest extends FormRequest
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
        $canal = $this->route('canal');

        return $canal === null
            || auth()->user()->can('manage', $canal);
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
        $canal = $this->route('canal');

        return [
            'title' => [
                'required', 'string', 'max:191', 'min:3',
                Rule::unique('organizations', 'title')->ignore($canal),
            ],
            'description'      => 'nullable|string',
            'street'           => 'nullable|string|max:191',
            'phone'            => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9 ()-]{6,20}$/'],
            'email'            => 'nullable|email|max:100',
            'url_www'          => 'nullable|string|max:191',
            'mod_title'        => 'nullable|string|max:20',
            'village_id'       => 'required|integer|exists:villages,id',
            // Do oboch polí patrí ID, nie adresa kanála. Adresu (aj s @handle)
            // prepisuje prepareForValidation() na ID; čo sa preložiť nedá,
            // sa sem dostane nezmenené a skončí chybovou hláškou — inak by
            // denný import na takom kanáli padal na 403 od YouTube.
            'youtube_channel'  => ['nullable', 'string', 'regex:' . ChannelId::PATTERN, 'max:40'],
            'youtube_playlist' => ['nullable', 'string', 'regex:' . PlaylistId::PATTERN, 'max:40'],
            // Vlastnosti kanála, ktoré do 9/2026 niesli updatery. Zaradenie
            // vidí každý správca, deň importu a smerovanie videí len admin —
            // kontrolu role robí controller, tu ide len o tvar dát.
            'denomination'     => ['nullable', Rule::enum(Denomination::class)],
            'import_day'       => 'nullable|integer|between:0,6',
            'post_section'     => ['nullable', Rule::enum(CanalSection::class)],
            // `users` a `published` sa vykresľujú len v @can('superadmin') bloku
            // formulára (resources/views/dashboard/canals/edit.blade.php).
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
            'youtube_channel.regex' => 'Kanál sa nedal určiť. Zadajte ID kanála (UC…) alebo adresu kanála na YouTube.',
            'youtube_playlist.regex' => 'Playlist sa nedal určiť. Zadajte ID playlistu (PL…) alebo jeho adresu na YouTube.',
        ];
    }

    /**
     * Do formulára sa dá vložiť aj adresa kanála — YouTube ju však ako
     * `channelId` neprijme (403 „The request is not properly authorized"),
     * takže import kanála padal každý deň. Adresu preto prepíšeme na ID
     * ešte pred validáciou; handle (@meno) doloží ChannelId z API.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('youtube_channel')) {
            $this->merge(['youtube_channel' => $this->channelId($this->input('youtube_channel'))]);
        }

        if ($this->has('youtube_playlist')) {
            $playlist = trim((string) $this->input('youtube_playlist'));

            $this->merge([
                'youtube_playlist' => $playlist === ''
                    ? null
                    : (PlaylistId::fromInput($playlist) ?? $playlist),
            ]);
        }
    }

    private function channelId($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            // Nerozlúsknutý vstup necháme tak, ako prišiel — pravidlo regex
            // na ňom vypíše hlášku, ktorú správca uvidí pri poli.
            return ChannelId::resolve($value) ?? $value;
        } catch (\Throwable $e) {
            // Nedostupné YouTube API nesmie zhodiť ukladanie kanála; ID
            // zapísané rovno do poľa prejde aj bez dopytu.
            Log::warning('Preklad adresy kanála YouTube na ID zlyhal: ' . $e->getMessage(), [
                'value' => $value,
            ]);

            return ChannelId::fromInput($value) ?? $value;
        }
    }

    public function save()
    {
        // Zakladá sa len z overených polí. `except()` prepúšťalo aj _token,
        // _method a čokoľvek iné, čo prišlo v tele požiadavky.
        //
        // Zakladajúci formulár ponúka len zaradenie kanála; deň importu
        // a smerovanie videí nastavuje admin až v úprave, preto tu ostávajú
        // na predvolených hodnotách stĺpca.
        $data = collect($this->validated())
            ->except(['users', 'published', 'import_day', 'post_section'])
            ->all();

        return auth()->user()->organizations()->create($data);
    }
}
