<?php

namespace App\Http\Requests\Client\Web;

use Illuminate\Foundation\Http\FormRequest;

class WarmUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'device_id' => 'required|exists:devices,id',
            'device_list_id' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => __('The warm-up name is required.'),
            'device_id.required' => __('Please select a device.'),
            'device_id.exists'   => __('Please select a device.'),
            'device_list_id'   => __('Please select helper device.'),
        ];
    }
}
