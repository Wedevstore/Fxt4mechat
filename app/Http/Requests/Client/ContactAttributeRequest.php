<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class ContactAttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Adjust this according to your authorization needs
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // For update requests, ensure you exclude the current record's ID from the unique check
        $id = $this->route('id'); // Assuming the route parameter is 'id'

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                $id ? 'unique:contact_attributes,title,' . $id : 'unique:contact_attributes,title'
            ],
            'type' => 'required|in:text,number,email,url,date,time',
        ];
    }

    /**
     * Customize the error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => __('the_title_field_is_required'),
            'title.string'   => __('the_title_must_be_a_string'),
            'title.max'      => __('title_may_not_be_greater_than_255_characters'),
            'title.unique'   => __('title_has_already_been_taken'),
            'type.required' => __('type_field_is_required'),
            'type.in'       => __('selected_type_is_invalid'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'title'  => __('Title'),
            'type'  => __('Type'),
        ];
    }
}
