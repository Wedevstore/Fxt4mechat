<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SMSProviderRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'active_sms_provider' => 'required|string',
        ];
        switch ($this->input('active_sms_provider')) {
            case 'Twilio':
                $rules = array_merge($rules, [
                    'twilio_sms_sid' => 'required|string',
                    'twilio_sms_auth_token' => 'required|string',
                    'valid_twilio_sms_number' => 'required|string',
                ]);
                break;
            case 'Fast2SMS':
                $rules = array_merge($rules, [
                    'fast2_api_link' => 'required|url',
                    'fast_2_auth_key' => 'required|string',
                    'fast_2_entity_id' => 'required|string',
                    'fast_2_route' => 'required|string',
                    'fast_2_language' => 'required|string',
                    'fast_2_sender_id' => 'nullable|string',
                ]);
                break;
            case 'REVESystems':
                $rules = array_merge($rules, [
                    'reve_systems_api_link' => 'required|url',
                    'reve_sms_api_key' => 'required|string',
                    'reve_secret_key' => 'required|string',
                    'reve_sender_id' => 'nullable|string',
                ]);
                break;
            case 'MIMO':
                $rules = array_merge($rules, [
                    'mimo_username' => 'required|string',
                    'mimo_sms_password' => 'required|string',
                    'mimo_sms_sender_id' => 'required|string',
                ]);
                break;
            case 'Nexmo':
                $rules = array_merge($rules, [
                    'nexmo_sms_key' => 'required|string',
                    'nexmo_sms_secret_key' => 'required|string',
                ]);
                break;
            case 'SSLWireless':
                $rules = array_merge($rules, [
                    'ssl_sms_api_link' => 'required|url',
                    'ssl_sms_api_token' => 'required|string',
                    'ssl_sms_sid' => 'required|string',
                ]);
                break;
            case 'SMSala':
                $rules = array_merge($rules, [
                    'sms_ala_api_link' => 'required|url',
                    'sms_ala_auth_id' => 'required|string',
                    'sms_ala_password' => 'required|string',
                    'sms_ala_type' => 'required|string',
                    'sms_ala_sender_id' => 'nullable|string',
                ]);
                break;
            case 'BudgetSMS':
                $rules = array_merge($rules, [
                    'budgetsms_api_link' => 'required|url',
                    'budgetsms_username' => 'required|string',
                    'budgetsms_user_id' => 'required|string',
                    'budgetsms_handle' => 'required|string',
                    'budgetsms_sender_id' => 'nullable|string',
                ]);
                break;
            case 'NobelSMS':
                $rules = array_merge($rules, [
                    'nobel_sms_api_link' => 'required|url',
                    'nobel_sms_username' => 'required|string',
                    'nobel_sms_password' => 'required|string',
                ]);
                break;
            case 'Infobip':
                $rules = array_merge($rules, [
                    'infobip_api_link' => 'required|url',
                    'infobip_api_key' => 'required|string',
                    'infobip_entity_id' => 'required|string',
                    'infobip_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Plivo':
                $rules = array_merge($rules, [
                    'plivo_auth_id' => 'required|string',
                    'plivo_auth_token' => 'required|string',
                    'plivo_sender_id' => 'nullable|string',
                ]);
                break;
            case 'TextLocal':
                $rules = array_merge($rules, [
                    'textlocal_api_link' => 'required|url',
                    'textlocal_api_key' => 'required|string',
                    'textlocal_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Msg91':
                $rules = array_merge($rules, [
                    'msg91_api_link' => 'required|url',
                    'msg91_auth_key' => 'required|string',
                    'msg91_flow_id' => 'required|string',
                ]);
                break;
            case 'ValueFirst':
                $rules = array_merge($rules, [
                    'valuefirst_api_link' => 'required|url',
                    'valuefirst_username' => 'required|string',
                    'valuefirst_password' => 'required|string',
                    'valuefirst_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Exotel':
                $rules = array_merge($rules, [
                    'exotel_api_link' => 'required|url',
                    'exotel_sid' => 'required|string',
                    'exotel_token' => 'required|string',
                    'exotel_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Karix':
                $rules = array_merge($rules, [
                    'karix_api_link' => 'required|url',
                    'karix_auth_id' => 'required|string',
                    'karix_auth_token' => 'required|string',
                    'karix_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Gupshup':
                $rules = array_merge($rules, [
                    'gupshup_api_link' => 'required|url',
                    'gupshup_userid' => 'required|string',
                    'gupshup_password' => 'required|string',
                    'gupshup_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Kaleyra':
                $rules = array_merge($rules, [
                    'kaleyra_api_link' => 'required|url',
                    'kaleyra_api_key' => 'required|string',
                    'kaleyra_sender_id' => 'nullable|string',
                ]);
                break;
            case 'RouteMobile':
                $rules = array_merge($rules, [
                    'routemobile_api_link' => 'required|url',
                    'routemobile_username' => 'required|string',
                    'routemobile_password' => 'required|string',
                    'routemobile_sender_id' => 'nullable|string',
                ]);
                break;
            case 'SMSGlobal':
                $rules = array_merge($rules, [
                    'smsglobal_api_link' => 'required|url',
                    'smsglobal_username' => 'required|string',
                    'smsglobal_password' => 'required|string',
                    'smsglobal_sender_id' => 'nullable|string',
                ]);
                break;
            case 'BulkSMS':
                $rules = array_merge($rules, [
                    'bulksms_api_link' => 'required|url',
                    'bulksms_username' => 'required|string',
                    'bulksms_password' => 'required|string',
                    'bulksms_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Vonage':
                $rules = array_merge($rules, [
                    'vonage_api_key' => 'required|string',
                    'vonage_api_secret' => 'required|string',
                    'vonage_sender_id' => 'nullable|string',
                ]);
                break;
            case 'EasySendSMS':
                $rules = array_merge($rules, [
                    'easysendsms_api_link' => 'required|url',
                    'easysendsms_username' => 'required|string',
                    'easysendsms_password' => 'required|string',
                    'easysendsms_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Callr':
                $rules = array_merge($rules, [
                    'callr_api_link' => 'required|url',
                    'callr_api_login' => 'required|string',
                    'callr_api_password' => 'required|string',
                ]);
                break;
            case 'CMCOM':
                $rules = array_merge($rules, [
                    'cm_api_link' => 'required|url',
                    'cm_product_token' => 'required|string',
                    'cm_sender_id' => 'required|string',
                ]);
                break;
            case 'AfricasTalking':
                $rules = array_merge($rules, [
                    'africastalking_api_link' => 'required|url',
                    'africastalking_api_key' => 'required|string',
                    'africastalking_username' => 'required|string',
                    'africastalking_sender_id' => 'nullable|string',
                ]);
                break;
            case '1s2u':
                $rules = array_merge($rules, [
                    '1s2u_api_link' => 'required|url',
                    '1s2u_username' => 'required|string',
                    '1s2u_password' => 'required|string',
                    '1s2u_sender_id' => 'nullable|string',
                ]);
                break;
            case 'KeccelSMS':
                $rules = array_merge($rules, [
                    'keccelsms_application_id' => 'required|string',
                    'keccelsms_password' => 'required|string',
                    'keccelsms_sender_id' => 'nullable|string',
                ]);
                break;
            case 'Gatewayapi':

                $rules = array_merge($rules, [
                    'gatewayapi_api_link' => 'required|url',
                    'gatewayapi_api_token' => 'required|string',
                    'gatewayapi_sender_id' => 'nullable|string',
                ]);

                break;

            case 'Custom':
                $rules = array_merge($rules, [
                    'custom_api_link' => 'required|url',
                    'custom_authorization' => 'required|string',
                    'custom_is_json' => 'required|string',
                    'custom_ssl_certificate_verify' => 'required|string',
                    'custom_content_type' => 'required|string',
                    'custom_content_type_accept' => 'required|string',
                    'custom_destination_number_param' => 'required|string',
                    'custom_message_param' => 'required|string',
                ]);
                break;

            default:

                break;
        }
        return $rules;
    }
}
