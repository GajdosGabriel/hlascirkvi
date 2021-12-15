<?php

namespace App\Http\Requests;

use App\Image;
use App\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Services\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostSaveRequest extends FormRequest
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

    public function __construct()
    {
        $this->post = new EloquentPostRepository;
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
                'body' => 'required|string|min:3',
                'updaters' => 'required|integer|exists:updaters,id',
                'organization_id' => 'required|integer|exists:organizations,id',
            ];
    }


    public function save()
    {
        $post = $this->post->create($this->except(['picture', 'updaters']));
        $post->updaters()->sync($this->get('updaters') ?: []);
        (new Form($post, $this))->handler();
        return $post;
    }

    public function update($id)
    {
        $post = $this->post->update($id, $this->except(['picture', 'updaters']));
        $post->updaters()->sync($this->get('updaters') ?: []);
        (new Form($post, $this))->handler();
        return $post;
    }
}
