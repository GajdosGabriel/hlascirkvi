<?php

namespace App\Http\Requests;

use App\Services\EventImageGenerator;
use App\Services\Form;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Eloquent\EloquentEventRepository;

class StoreEventRequest extends FormRequest
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
        $this->event = new EloquentEventRepository;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:250',
            'start_at' => 'required|date|after:yesterday',
            'end_at' => 'required|date|after:yesterday',
            'organization_id' => 'required|integer|exists:organizations,id',
            'registration' => 'required',
            'entryFee' => 'required',
            'published' => 'required'
        ];

        return $rules;
    }


    public function messages()
    {
        $messages = [
            'start_at.required' => 'Začiatok akcie musí byť vyšší ako dnešný.'
        ];

        return $messages;
    }

    public function save()
    {
        $event = $this->event->create($this->except(['picture', 'file']));
        // $event->update();
        (new Form($event, $this))->handler();
    }
}
