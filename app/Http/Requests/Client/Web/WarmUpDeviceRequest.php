<?php

namespace App\Http\Requests\Client\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarmUpDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warmup_contacts', 'device_id')
                    ->ignore($this->route('id')),
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'device_id.required' => __('The device is required.'),
            'device_id.unique'   => __('This device already exist.'),
        ];
    }
}
