<?php

namespace App\Http\Requests;

use App\Services\Youtube\PlaylistId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * Semináre boli jediná agenda kanála bez FormRequestu — `store` aj `update`
 * brali `$request->all()` na modeli s $guarded = ['id'], takže sa dalo poslať
 * aj `canal_id` a `published`.
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

    protected function prepareForValidation()
    {
        if ($this->has('youtube_playlist')) {
            $input = trim((string) $this->input('youtube_playlist'));
            $this->merge(['youtube_playlist' => $input === '' ? null : (PlaylistId::fromInput($input) ?? $input)]);
        }
        // Prepínač posiela `Date.now()` (milisekundy), vypnutie prázdny reťazec.
        if ($this->has('published')) {
            $published = $this->input('published');

            $this->merge([
                'published' => is_numeric($published)
                    ? Carbon::createFromTimestampMs((int) $published)->toDateTimeString()
                    : $published,
            ]);
        }
    }

    public function rules()
    {
        // Prepínač zverejnenia posiela cez PUT len `published`, takže pri
        // úprave sa validujú len tie polia, ktoré naozaj prišli.
        $title = $this->isMethod('POST') ? 'required' : 'sometimes|required';

        return [
            'title'            => $title . '|string|min:3|max:255',
            'description'      => 'nullable|string|max:5000',
            'kind'             => 'sometimes|required|in:seminar,collection',
            'youtube_playlist' => ['nullable', 'string', 'regex:' . PlaylistId::PATTERN, 'max:255'],
            'published'        => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Názov je povinný',
            'title.min'      => 'Názov musí obsahovať aspoň tri znaky',
            'youtube_playlist.regex' => 'Neplatné ID playlistu',
        ];
    }
}
