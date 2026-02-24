<?php

namespace App\Http\Requests\Client\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarmUpNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warmup_contacts', 'phone_number')
                    ->where(function ($query) {
                        return $query->where('warmup_id', $this->warmup_id);
                    })
                    ->ignore($this->route('id')),
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'name.required'         => __('The name is required.'),
            'phone_number.required' => __('The phone number is required.'),
            'phone_number.unique'   => __('This phone number already exists in warmup contacts.'),
        ];
    }
}
