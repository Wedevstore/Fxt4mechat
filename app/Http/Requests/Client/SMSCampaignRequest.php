<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SMSCampaignRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize the request
        return true;
    }

    public function rules()
    {
        return [
            'campaign_name'      => 'required|string',
            'contact_list_ids'   => 'nullable|array',
            'contact_list_ids.*' => 'nullable|integer|exists:contacts_lists,id',
            'segment_ids'      => 'nullable|array',
            'segment_ids*'     => 'nullable|integer|exists:segments,id',
            'country_id'         => 'nullable|integer|exists:countries,id',
            'template_id'        => 'nullable|integer|exists:sms_templates,id',
            'body'               => 'required|string',
            'schedule_time'      => ['nullable', 'required_if:send_scheduled,1', 'date_format:Y-m-d H:i:s'],
        ];
    }
    

    public function messages()
    {
        return [
            'contact_id.required' => __('please_select_at_least_one_contact'),
            'body.required'       => __('the_sms_body_is_required'),
            'body.max'            => __('the_sms_body_must_not_exceed_160_haracters'),
        ];
    }
    
}
