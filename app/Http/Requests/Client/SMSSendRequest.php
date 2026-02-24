<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SMSSendRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize the request
        return true;
    }

    public function rules()
    {
        return [
            // 'contact_id'   => 'required|array|min:1',
            'contact_id' => 'required|integer|exists:contacts,id',
            'template_id'  => 'nullable|integer|exists:sms_templates,id',
            'body'         => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'contact_id.required' => __('please_select_at_least_one_contact'),
            'body.required'       => __('the_sms_body_is_required'),
            'body.max'            => __('the_sms_body_must_not_exceed_160_characters'),
        ];
    }
    
}
