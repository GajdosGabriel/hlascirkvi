<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Post;
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
            'body' => 'bail|required|min:3',
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
                    ->whereNull('deleted_at'),
            ],
        ];

        if (auth()->guest()) {
            $rules['email'] = 'required|email|max:255';
        }

        return $rules;
    }

    public function save($post)
    {
        $data = $this->only('body');

        if ($this->filled('parent_id')) {
            // Vlákno má jednu úroveň: odpoveď na odpoveď patrí pod hlavný komentár.
            $parent = Comment::find($this->input('parent_id'));
            $data['parent_id'] = $parent->parent_id ?? $parent->id;
        }

        return $post->addComment($data);
    }
}
