<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Post;
use App\Rules\IsHuman;
use App\Support\HumanCheck;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SaveCommentsRequest extends FormRequest
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
        $rules = [
            'body' => 'bail|required|min:3|max:2000',
        ];

        // Úprava mení len text (PostCommentController::update), no Vue posiela
        // celý komentár aj s parent_id. Po zmazaní hlavného komentára by tak
        // odpoveď pod ním už nešlo upraviť.
        if (! $this->isMethod('POST')) {
            return $rules;
        }

        $rules += [
            // Odpovedať sa dá len na zverejnený komentár toho istého príspevku.
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')
                    ->where('commentable_type', Post::class)
                    ->where('commentable_id', $this->route('post')?->getKey())
                    ->whereNull('deleted_at')
                    ->whereNotNull('published'),
            ],
        ];

        if (auth()->guest()) {
            $rules['email'] = 'required|email|max:255';
            $rules[HumanCheck::STAMP] = ['required', new IsHuman];
        }

        return $rules;
    }

    public function attributes()
    {
        return ['body' => 'Váš komentár'];
    }

    /**
     * Komentár je čistý text. API ho vracia v pôvodnej podobe aj pre cudzích
     * klientov, takže značky sa odstránia už pri ukladaní.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => trim(strip_tags($this->input('body')))]);
        }
    }

    /** @return array{body: string, parent_id?: int, reply_to_id?: int} */
    public function commentData(): array
    {
        $data = $this->only('body');

        if ($this->filled('parent_id')) {
            // Vlákno má jednu úroveň: odpoveď na odpoveď patrí pod hlavný
            // komentár, no `reply_to_id` si pamätá, na ktorý sa reagovalo.
            $target = Comment::find($this->input('parent_id'));
            $data['parent_id'] = $target->parent_id ?? $target->id;
            $data['reply_to_id'] = $target->id;
        }

        return $data;
    }
}
