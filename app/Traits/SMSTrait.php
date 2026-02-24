<?php

namespace App\Traits;

use stdClass;
use Twilio\Rest\Client;
use App\Models\SMSHistory;
use Illuminate\Support\Str;
use Vonage\SMS\Message\SMS;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Vonage\Client\Credentials\Basic;
use Psr\Http\Client\ClientExceptionInterface;

trait SMSTrait
{
    public function test($request): bool
    {
        $template_id  = arrayCheck('template_id', $request) ? $request['template_id'] : '';
        $phone_number = '+' . $request['test_number'];
        $provider     = sms_setting('active_sms_provider');
        $sms_body     = 'This is a text message from ' . $provider . ' for checking configuration';

        if ($this->send($phone_number, $sms_body, $template_id, $id = null)) {
            return true;
        } else {
            return false;
        }
    }

    private function send($phone_number, $sms_body, $template_id = '', $smsHistoryId = null)
    {
        $provider = sms_setting('active_sms_provider');
        if ($provider == 'Twilio') { //Has Callback
            $sid            = sms_setting('twilio_sms_sid');
            $token          = sms_setting('twilio_sms_auth_token');
            $sms_number     = sms_setting('valid_twilio_sms_number');
            $client = new Client($sid, $token);
            try {
                $message = $client->messages->create(
                    $phone_number,
                    [
                        'from' => $sms_number,
                        'body' => $sms_body,
                        'statusCallback' => route('sms.dlr.twilio'),
                    ]
                );
                $messageId = $message->sid;
                $status = $message->status;
                if ($status == 'queued' || $status == 'accepted') {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $messageId,
                        'send_at' => Carbon::now(),
                    ]);
                }
                return true;
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        } elseif ($provider == 'Fast2SMS') {
            $url = sms_setting('fast2_api_link') != '' ? sms_setting('fast2_api_link') : 'https://www.fast2sms.com/dev/bulkV2';
            $sender_id      = sms_setting('fast_2_sender_id');
            $entity_id      = sms_setting('fast_2_entity_id');
            $language       = sms_setting('fast_2_language');
            $route          = sms_setting('fast_2_route');
            $auth_key       = sms_setting('fast_2_auth_key');

            // Remove country code if present
            if (strpos($phone_number, '+91') !== false) {
                $phone_number = substr($phone_number, 3);
            }
            // Prepare fields based on the route
            if ($route == 'dlt_manual') {
                $fields = [
                    'sender_id'   => $sender_id,
                    'message'     => $sms_body,
                    'template_id' => $template_id,
                    'entity_id'   => $entity_id,
                    'language'    => $language,
                    'route'       => $route,
                    'numbers'     => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                ];
            } else {
                $fields = [
                    'sender_id' => $sender_id,
                    'message'   => $sms_body,
                    'language'  => $language,
                    'route'     => $route,
                    'numbers'   => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                ];
            }

            $curl = \curl_init();
            \curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => json_encode($fields),
                CURLOPT_HTTPHEADER     => [
                    "authorization: $auth_key",
                    'accept: */*',
                    'cache-control: no-cache',
                    'content-type: application/json',
                ],
            ]);
            $response = \curl_exec($curl);
            $err = \curl_error($curl);
            \curl_close($curl);
            $responseData = json_decode($response);
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            if (isset($responseData->return)) {
                if ($responseData->return == 'success') {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData->message_id ?? null,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $responseData->message ?? 'Failed to send SMS',
                    ]);
                    return false;
                }
            } else {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'REVESystems') {
            $api_key = sms_setting('reve_sms_api_key');
            $secret_key = sms_setting('reve_secret_key');
            $url = sms_setting('reve_systems_api_link') != '' ? sms_setting('reve_systems_api_link') : 'http://apismpp.revesms.com/sendtext';
            $sender_id = sms_setting('reve_sender_id') ?: 'SENDER_ID';
            $phone_number = preg_replace('/^(\+88|88)/', '', $phone_number);
            $phone_number = preg_replace('/-/', '', $phone_number);
            $phone_number = preg_replace('/(\s)/', '', $phone_number);
            $params = [
                'apikey' => $api_key,
                'secretkey' => $secret_key,
                'callerID' => $sender_id,
                'toUser' => $phone_number,
                'messageContent' => $sms_body,
            ];
            // dd($params);
            $ch           = \curl_init();
            $data         = http_build_query($params);
            $url       = $url . '?' . $data;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 80);
            $result       = \curl_exec($ch);
            \curl_close($ch);
            $responseData = json_decode($result);
            if ($responseData->Status == '0') {
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData->Message_ID,
                    ]);
                }
                return true;
            } else if (in_array($responseData->Status, ['109', '108', '114', '101', '1', '5400'])) {
                $errorMessage = '';
                switch ($responseData->Status) {
                    case '109':
                        $errorMessage = 'User not provided or deleted';
                        break;
                    case '108':
                        $errorMessage = 'Wrong password or not provided';
                        break;
                    case '114':
                        $errorMessage = 'Inappropriate request parameter';
                        break;
                    case '101':
                        $errorMessage = 'Internal server error';
                        break;
                    case '1':
                        $errorMessage = 'Request failed';
                        break;
                    case '5400':
                        $errorMessage = 'Source IP has not been allowed';
                        break;
                }
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $errorMessage,
                    ]);
                }
                return false;
            } else {
                // General failure case
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update(['status' => 'failed']);
                }
                return false;
            }
        } elseif ($provider == 'MIMO') {
            $token = $this->getToken();
            if (!$token) {
                // Handle token retrieval failure
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => 'Failed to retrieve authentication token.',
                    ]);
                }
                return false;
            }

            // Send the SMS message
            $response = $this->sendMessages($phone_number, $sms_body, $token);

            // Check the response and update SMS history accordingly
            if (isset($response['status']) && $response['status'] == 'success') {
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $response['message_id'] ?? null, // Capture message ID if available
                        'send_at' => Carbon::now(),
                    ]);
                }
                $this->logout($token); // Logout only on success
                return true;
            } else {
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $response['message'] ?? 'Failed to send SMS',
                    ]);
                }
                $this->logout($token);
                return false;
            }
        } elseif ($provider == 'Nexmo') {
            $sms_key = sms_setting('nexmo_sms_key');
            $secret_key = sms_setting('nexmo_sms_secret_key');
            try {
                $basic    = new \Vonage\Client\Credentials\Basic($sms_key, $secret_key);
                $client   = new \Vonage\Client($basic);
                $response = $client->sms()->send(
                    new \Vonage\SMS\Message\SMS($phone_number, '', $sms_body)
                );
                $message  = $response->current();
                $messageId = $message->getMessageId();
                $status = $message->getStatus();

                // Check if the message was successfully sent
                if ($messageId && $status == 0) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $messageId,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Update history with failure details
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $message->getErrorText() ?? __('unknown_error'),
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                // Catch any exceptions and log them
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                return $e->getMessage();
            }
        } elseif ($provider == 'SSLWireless') {

            $url = sms_setting('ssl_sms_api_link') != '' ? sms_setting('ssl_sms_api_link') : 'http://sms.sslwireless.com/pushapi/dynamic/server.php';
            $token = sms_setting('ssl_sms_api_token');
            $sid = sms_setting('ssl_sms_sid');
            $csms_id = Str::random(5) . now()->format('YmdHis');
            $data = [
                'api_token' => $token,
                'sid' => $sid,
                'msisdn' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'sms' => $sms_body,
                'csms_id' => $csms_id,
            ];
            $data = json_encode($data);

            $ch = \curl_init();
            \curl_setopt($ch, CURLOPT_URL, $url);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            \curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            \curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'accept: application/json',
            ]);
            $response = \curl_exec($ch);
            \curl_close($ch);
            $responseData = json_decode($response);
            // Check response status
            if (isset($responseData->status)) {
                // Update database based on the response
                if ($responseData->status == 'success') {
                    // Mark as sent
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData->message_id ?? null,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Mark as failed
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $responseData->message ?? 'Failed to send SMS',
                    ]);
                    return false;
                }
            } else {
                // Handle unexpected response structure
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'SMSala') {

            $url = sms_setting('sms_ala_api_link') != '' ? sms_setting('sms_ala_api_link') : 'http://api.smsala.com/api/SendSMS';
            $auth_id = sms_setting('sms_ala_auth_id');
            $password = sms_setting('sms_ala_password');
            $sender_id = sms_setting('sms_ala_sender_id');

            $fields = [
                'api_id'       => $auth_id,
                'api_password' => $password,
                'sms_type'     => 'P',
                'phonenumber'  => $phone_number,
                'sender_id'    => $sender_id,
                'textmessage'  => $sms_body,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            // Check for errors in the cURL request
            if ($err) {
                // Log the error and update the database as needed
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            $responseData = json_decode($response, true);
            // Check response status
            if (isset($responseData['status'])) {
                if ($responseData['status'] == 'success') {
                    // Update database on success
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData['message_id'] ?? null,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Log failure and error message
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $responseData['message'] ?? 'Failed to send SMS',
                    ]);
                    return false;
                }
            } else {
                // Handle unexpected response structure
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'BudgetSMS') {
            // Get configuration settings
            $csms_id = Str::random(2) . now()->format('YmdHis');
            $url = sms_setting('budgetsms_api_link') ?: 'https://api.budgetsms.net/sendsms/';
            $username = sms_setting('budgetsms_username');
            $userid = sms_setting('budgetsms_user_id');
            $handle = sms_setting('budgetsms_handle');
            $sender = sms_setting('budgetsms_sender_id');
            // Prepare fields for the API request
            $fields = [
                'username' => $username,
                'userid' => $userid,
                'handle' => $handle,
                'msg' => $sms_body,
                'from' => $sender,
                'customid' => $csms_id,
                'to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
            ];
            // Ensure fields are URL encoded
            $encodedFields = http_build_query($fields);
            // Initialize cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedFields);
            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            // Handle cURL errors
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            // Decode the JSON response
            $responseData = json_decode($response, true);
            // Check response structure
            if (isset($responseData['status'])) {
                if ($responseData['status'] === 'success') {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData['message_id'] ?? null,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Log failure and error message based on the response
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $this->getBudgetSMSErrorMessage($responseData['message']),
                    ]);
                    return false;
                }
            } else {
                // Handle unexpected response structure
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'NobelSMS') {
            $url = sms_setting('nobel_sms_api_link') != '' ? sms_setting('nobel_sms_api_link') : 'https://api.nobelsms.com/rest/send_sms';
            $username = sms_setting('nobel_sms_username');
            $password = sms_setting('nobel_sms_password');
            $fields = [
                'username' => $username,
                'password' => $password,
                'to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'message' => $sms_body,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            // Error handling for cURL request
            if ($err) {
                // Log the error and update the database as needed
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            // Decode response to check for success or failure
            $responseData = json_decode($response, true); // Decode as an associative array
            // Check response status (assuming NobelSMS returns a status field)
            if (isset($responseData['status'])) {
                if ($responseData['status'] == 'success') {
                    // Update database on success
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData['message_id'] ?? null, // Assuming response includes message_id
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Log failure and error message
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $responseData['message'] ?? 'Failed to send SMS',
                    ]);
                    return false;
                }
            } else {
                // Handle unexpected response structure
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'Infobip') { //Has Callback
            $url = sms_setting('infobip_api_link') != '' ? sms_setting('infobip_api_link') : 'https://89g329.api.infobip.com/sms/2/text/advanced';
            $api_key = sms_setting('infobip_api_key');
            $sender = sms_setting('infobip_sender_id');
            $messageId = Str::random(2) . now()->format('YmdHis');
            $destination = [
                'messageId' => $messageId,
                'to'        => $phone_number,
            ];
            // Prepare the payload
            $fields = [
                'messages' => [
                    [
                        'from' => $sender,
                        "destinations"      => [$destination],
                        // 'to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                        'text' => $sms_body,
                        'notifyUrl' => route('sms.dlr.infobip'),
                        'notifyContentType' => 'application/json',
                    ]
                ]
            ];
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Authorization: App $api_key"
            ]);
            // Execute cURL request
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            // Error handling for cURL request
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            // Decode response to check for success or failure
            $responseData = json_decode($response, true); // Decode as an associative array
            // Check response status
            if (isset($responseData['messages'][0]['status']['groupId'])) {
                // Check if the message status is successful
                if ($responseData['messages'][0]['status']['groupId'] == 0) { // 0 indicates success
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData['messages'][0]['messageId'] ?? null, // Assuming response includes messageId
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Log failure and error message
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $responseData['messages'][0]['status']['description'] ?? 'Failed to send SMS',
                    ]);
                    return false;
                }
            } else {
                // Handle unexpected response structure
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'Plivo') { //Has Callback
            $auth_id = sms_setting('plivo_auth_id');
            $auth_token = sms_setting('plivo_auth_token');
            $sender_id = sms_setting('plivo_sender_id');
            // Initialize Plivo client
            $client = new \Plivo\RestClient($auth_id, $auth_token);
            try {
                // Send SMS
                $response = $client->messages->create(
                    $sender_id,
                    [$phone_number],
                    $sms_body,
                    ['url' => route('sms.dlr.plivo')],
                );

                // Check response status
                if ($response && $response->status == '202') {
                    // Successful response
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $response->message_uuid[0] ?? null, // Capture message UUID if available
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Handle unsuccessful response
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => 'Failed to send SMS',
                    ]);
                    return false;
                }
            } catch (\Plivo\Exceptions\PlivoRestException $e) {
                // Handle Plivo API exception
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(), // Capture and log error message
                ]);
                return false;
            }
        } elseif ($provider == 'TextLocal') { //Has Callback
            $url = sms_setting('textlocal_api_link') != '' ? sms_setting('textlocal_api_link') : 'https://api.txtlocal.com/send/';
            $api_key = sms_setting('textlocal_api_key');
            $sender_id = sms_setting('textlocal_sender_id');
            $messageId = Str::random(2) . now()->format('YmdHis');
            // Prepare the fields for the request
            $fields = [
                'apikey' => $api_key,
                'numbers' => $phone_number,
                'message' => rawurlencode($sms_body), // Encode message to be URL safe
                // 'message' => $sms_body, // Encode message to be URL safe
                'sender' => $sender_id,
                'custom' => $messageId,
                'receipt_url' => route('sms.dlr.textlocal'), // Optionally, you can set a delivery receipt URL here
            ];

            try {
                // Initialize cURL request
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch); // Execute the request
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code
                curl_close($ch);

                // Decode the response from TextLocal
                $response_data = json_decode($response, true);

                // Check if the request was successful
                if ($http_code == 200 && isset($response_data['status']) && $response_data['status'] == 'success') {
                    // Update the SMS history on successful sending
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $response_data['messageid'] ?? null, // Save message ID if available
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    // Handle errors from the response
                    $error = $response_data['message'] ?? 'Unknown error';
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $error,
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                // Catch any exceptions during the process
                $error = $e->getMessage() ?? 'Unknown error';
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $error,
                ]);
                return false;
            }
        } elseif ($provider == 'Msg91') {
            $url = sms_setting('msg91_api_link') != '' ? sms_setting('msg91_api_link') : 'https://api.msg91.com/api/v5/flow/';
            $auth_key = sms_setting('msg91_auth_key');
            $flow_id = sms_setting('msg91_flow_id');
            $sender_id = sms_setting('msg91_sender_id');
            $route = sms_setting('msg91_route');
            $country_code = '91';
            $fields = [
                'authkey' => $auth_key,
                'mobiles' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'message' => $sms_body,
                'sender' => $sender_id,
                'route' => $route,
                'country' => $country_code,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response_data = json_decode($response, true);
            // Check for success in the response
            if ($http_code == 200 && isset($response_data['type']) && $response_data['type'] == 'success') {
                // Update SMS history in the database on success
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'sent',
                    'message_id' => $response_data['message_id'] ?? null, // Capture message ID if available
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                // Handle errors from the response
                $error = $response_data['message'] ?? 'Unknown error';
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $error,
                ]);
                return false;
            }
        } elseif ($provider == 'ValueFirst') {
            $url = sms_setting('valuefirst_api_link') != '' ? sms_setting('valuefirst_api_link') : '';
            $username = sms_setting('valuefirst_username');
            $password = sms_setting('valuefirst_password');
            $sender = sms_setting('valuefirst_sender_id');
            $fields = [
                'username' => $username,
                'password' => $password,
                'to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'message' => $sms_body,
                'sender' => $sender,
            ];

            // Initialize cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch); // Capture any cURL error
            curl_close($ch);

            // Error handling for cURL request
            if ($err) {
                if ($smsHistoryId) {
                    SMSHistory::where('id', $smsHistoryId)->update(['status' => 'failed', 'error' => $err]);
                }
                return false;
            }

            // Decode the response
            $result = json_decode($response, true);

            // Check the response status
            if (isset($result['status'])) {
                if ($result['status'] === 'success') {
                    // Update SMS history to 'sent'
                    if ($smsHistoryId) {
                        SMSHistory::where('id', $smsHistoryId)->update(['status' => 'sent']);
                    }
                    return true;
                } else {
                    // Handle other status responses
                    if ($smsHistoryId) {
                        SMSHistory::where('id', $smsHistoryId)->update(['status' => 'failed', 'error' => $result['message'] ?? 'Unknown error']);
                    }
                    return false;
                }
            }

            // Default case for unexpected response
            if ($smsHistoryId) {
                SMSHistory::where('id', $smsHistoryId)->update(['status' => 'failed', 'error' => 'Invalid response format']);
            }
            return false;
        } elseif ($provider == 'Exotel') { //Has Callback
            $url = sms_setting('exotel_api_link') != '' ? sms_setting('exotel_api_link') : '';
            $sid = sms_setting('exotel_sid');
            $token = sms_setting('exotel_token');
            $sender = sms_setting('exotel_sender_id');
            $fields = [
                'From' => $sender,
                'To' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'Body' => $sms_body,
            ];
            // Initialize cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token"); // Basic authentication
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch); // Capture any cURL error
            curl_close($ch);

            // Error handling for cURL request
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }

            // Decode the response to check for success or failure
            $responseData = json_decode($response, true);

            // Check the response for success or failure
            if (isset($responseData['status']) && $responseData['status'] == 'success') {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'sent',
                    'message_id' => $responseData['message_id'] ?? null, // Capture message ID if available
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                // Log failure and error message
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['message'] ?? 'Failed to send SMS',
                ]);
                return false;
            }
        } elseif ($provider == 'Karix') {
            $url = sms_setting('karix_api_link') != '' ? sms_setting('karix_api_link') : 'https://api.karix.io/message/';
            $auth_id = sms_setting('karix_auth_id');
            $auth_token = sms_setting('karix_auth_token');
            $sender = sms_setting('karix_sender_id');
            $fields = [
                'channel' => 'sms',
                'source' => $sender,
                'destination' => [$phone_number],
                'content'     => [
                    'text' => $sms_body,
                ],
            ];
            // Initialize cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$auth_id" . ":" . "$auth_token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch); // Capture any cURL error
            curl_close($ch);

            // Error handling for cURL request
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }

            // Decode the response to check for success or failure
            $responseData = json_decode($response, true);

            // Check the response for success or failure
            if (isset($responseData['status']) && $responseData['status'] == 'queued') {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'delivered',
                    'message_id' => $responseData['account_uid'] ?? null, // Capture message ID if available
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                // Log failure and error message
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['error']['message'] ?? 'Failed to send SMS',
                ]);
                return false;
            }
        } elseif ($provider == 'Gupshup') {
            $url = sms_setting('gupshup_api_link') != '' ? sms_setting('gupshup_api_link') : 'https://enterprise.smsgupshup.com/sendsms/';
            $api_key = sms_setting('gupshup_api_key');
            $userid = sms_setting('gupshup_userid');
            $password = sms_setting('gupshup_password');
            $gupshup_sender = sms_setting('gupshup_sender_id');
            $messageId = Str::random(2) . now()->format('YmdHis');

            // Prepare the fields for the request
            $fields = [
                // 'apikey' => $api_key,
                'userid' => $userid,
                'password' => $password,
                'message' => $sms_body,
                'send_to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'method' => 'sendMessage',
                'format' => 'TEXT',
                'msg_id' => $messageId,
                'mask' => $gupshup_sender,
            ];

            // Initialize cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch); // Capture any cURL error
            curl_close($ch);

            // Error handling for cURL request
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            // Decode the response to check for success or failure
            $responseData = json_decode($response, true);
            // Check the response for success or failure
            if (isset($responseData['status']) && $responseData['status'] == 'success') {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'delivered',
                    'message_id' => $responseData['message_id'] ?? null, // Capture message ID if available
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                // Log failure and error message
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['message'] ?? 'Failed to send SMS',
                ]);
                return false;
            }
        } elseif ($provider == 'Kaleyra') {
            $api_key = sms_setting('kaleyra_api_key');
            $url = sms_setting('kaleyra_api_link') != '' ? sms_setting('kaleyra_api_link') : 'https://api-alerts.kaleyra.com/v4/';
            $sender = sms_setting('kaleyra_sender_id');
            // Prepare fields for the API request
            $fields = [
                'api_key' => $api_key,
                'method' => 'sms',
                'to' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'message' => $sms_body,
                'sender' => $sender,
            ];
            // Initialize cURL session
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            // Execute the request
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            // Handle cURL errors
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            // Decode response and handle success or failure
            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && $responseData['status'] == 'OK') {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'delivered',
                    'message_id' => $responseData['data'][0]['id'] ?? null,
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['message'] ?? 'Failed to send SMS',
                ]);
                return false;
            }
        } elseif ($provider == 'RouteMobile') {  //Has Callback
            $url = sms_setting('routemobile_api_link') != '' ? sms_setting('routemobile_api_link') : 'https://api.rmlconnect.net/bulksms/bulksms';
            $username = sms_setting('routemobile_username');
            $password = sms_setting('routemobile_password');
            $sender_id = sms_setting('routemobile_sender_id');

            // Prepare parameters
            $parameters = [
                'username' => $username,
                'password' => $password,
                'source' => $sender_id,
                'destination' => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'dlr' => 1,
                'type' => 0, // Regular text type
                'message' => $sms_body,
            ];
            $url = $url . '?' . http_build_query($parameters);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }
                // Decode JSON response
                $responseData = json_decode($response, true);
                if (is_array($responseData) && isset($responseData['status'])) {
                    // Switch based on response status
                    switch ($responseData['status']) {
                        case '1701':
                            $status = 'sent';
                            break;
                        case '1702':
                            $errorMessage = 'Invalid URL';
                            break;
                        case '1703':
                            $errorMessage = 'Invalid User or Password';
                            break;
                        case '1704':
                            $errorMessage = 'Invalid Type';
                            break;
                        case '1705':
                            $errorMessage = 'Invalid SMS';
                            break;
                        case '1706':
                            $errorMessage = 'Invalid receiver';
                            break;
                        case '1707':
                            $errorMessage = 'Invalid sender';
                            break;
                        case '1709':
                            $errorMessage = 'User Validation Failed';
                            break;
                        case '1710':
                            $errorMessage = 'Internal Error';
                            break;
                        case '1715':
                            $errorMessage = 'Response Timeout';
                            break;
                        case '1025':
                            $errorMessage = 'Insufficient Credit';
                            break;
                        default:
                            $errorMessage = 'Invalid request';
                            break;
                    }
                } else {
                    $errorMessage = 'Invalid response structure';
                }
                curl_close($ch);
            } catch (\Exception $e) {
                $error = $e->getMessage() ?? 'Unknown error';
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $error,
                ]);
                return false;
            }
            if (isset($status) && $status == 'sent') {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'sent',
                    'message_id' => $responseData['message_id'] ?? null,
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                $errorMessage = $errorMessage ?? 'Unknown Error';
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $errorMessage,
                ]);
                return false;
            }
        } elseif ($provider == 'SMSGlobal') { //Has Callback
            $url = sms_setting('smsglobal_api_link') != '' ? sms_setting('smsglobal_api_link') : 'https://api.smsglobal.com/http-api.php';
            $api_key = sms_setting('smsglobal_api_key');
            $username = sms_setting('smsglobal_username');
            $password = sms_setting('smsglobal_password');
            $sender = sms_setting('smsglobal_sender_id');
            $fields = [
                'action'  => 'sendsms',
                'user'    => $username,
                'password' => $password,
                'to'      => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'message' => $sms_body,
                'from'    => $sender,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error'  => $err,
                ]);
                return false;
            }
            if (substr_count($response, 'OK: 0') == 1) {
                // Extract the message IDs from the response
                preg_match('/Sent queued message ID: ([^ ]+).*SMSGlobalMsgID: ([^ ]+)/', $response, $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    $queuedMessageId = $matches[1];
                    $smsGlobalMsgId = $matches[2];
                    // Update database on success
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status'     => 'sent',
                        'message_id' => $smsGlobalMsgId, // Capture SMSGlobal message ID
                        'send_at'    => Carbon::now(),
                    ]);
                    return true;
                }
            } else {
                // Log failure and error message
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error'  => $response ?? 'Failed to send SMS',
                ]);
                return false;
            }
        } elseif ($provider == 'BulkSMS') { //Has Callback
            $url = sms_setting('bulksms_api_link') != '' ? sms_setting('bulksms_api_link') : 'https://api.bulksms.com/v1/messages';
            $username = sms_setting('bulksms_username');
            $password = sms_setting('bulksms_password');
            $sender_id = sms_setting('bulksms_sender_id');
            $parameters = [
                'longMessageMaxParts' => 6,
                'to'                  => $phone_number,
                'body'                => $sms_body,
            ];
            if ($sender_id) {
                $parameters['from'] = $sender_id;
            }
            // Set headers
            $headers = [
                'Content-Type:application/json',
                'Authorization:Basic ' . base64_encode("$username:$password"),
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . "?auto-unicode=true");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }
            $responseData = json_decode($response, true);
            if (isset($responseData['id']) && isset($responseData['status'])) {
                $messageId = $responseData['id'];
                $status = $responseData['status'];
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => $status === 'SENT' ? 'sent' : 'failed',
                    'message_id' => $messageId,
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['error'] ?? 'Unexpected response structure',
                ]);
                return false;
            }
        } elseif ($provider == 'Vonage') { //Has Callback
            $api_key = sms_setting('vonage_api_key');
            $api_secret = sms_setting('vonage_api_secret');
            $sender_id = sms_setting('vonage_sender_id');
            $client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic($api_key, $api_secret));
            $text = new \Vonage\SMS\Message\SMS($phone_number, $sender_id, $sms_body);
            try {
                $response = $client->sms()->send($text);
                $output = $response->current();
                if ($output->getStatus() == 0) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $output->getMessageId(),
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $output->getStatus(),
                    ]);
                }
            } catch (ClientExceptionInterface | \Vonage\Client\Exception\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
            return false;
        } elseif ($provider == 'EasySendSMS') { //Has Callback
            $url = sms_setting('easysendsms_api_link') != '' ? sms_setting('easysendsms_api_link') : 'https://api.easysendsms.app/bulksms';
            $username = sms_setting('easysendsms_username');
            $password = sms_setting('easysendsms_password');
            $sender_id = sms_setting('easysendsms_sender_id') ?? null;
            $sender_id = is_numeric($sender_id) ? preg_replace('/[^\w]/', '', $sender_id) : $sender_id;
            $parameters = http_build_query([
                'username' => $username,
                'password' => $password,
                'to'       => is_array($phone_number) ? implode(',', $phone_number) : $phone_number,
                'text'     => $sms_body,
                'type'     => 0,
                'from'     => $sender_id,
            ]);

            try {
                $url = $url . "?" . $parameters;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error'  => $err,
                    ]);
                    return false;
                }
                $responseData = json_decode($response, true);
                if (is_array($responseData) && !empty($responseData) && $responseData[0] === 'OK') {
                    $messageId = $responseData[1] ?? null;
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status'     => 'sent',
                        'message_id' => $messageId,
                        'send_at'    => Carbon::now(),
                    ]);
                    return true;
                }
                $errorCode = (int) filter_var($responseData[0], FILTER_SANITIZE_NUMBER_INT);
                $errorMsg = $this->getEasySendSMSErrorMessage($errorCode);
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error'  => $errorMsg,
                    'send_at' => Carbon::now(),
                ]);
                return false;
            } catch (\Exception $e) {
                // Handle any exceptions that may arise
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error'  => $e->getMessage() ?? 'Unknown error occurred',
                    'send_at' => Carbon::now(),
                ]);
                return false;
            }
        } elseif ($provider == 'Callr') { //Has Callback
            $url = sms_setting('callr_api_link') != '' ? sms_setting('callr_api_link') : 'https://api.callr.com/rest/v1.1/sms';
            $callr_api_login = sms_setting('callr_api_login');
            $callr_api_password = sms_setting('callr_api_password');
            $sender_id = sms_setting('callr_sender_id') ?? null;
            // Generate random user data
            $random_data = Str::random(2) . now()->format('YmdHis');;
            $options = new stdClass();
            $options->user_data = $random_data;

            // Normalize phone number
            $normalized_phone_number = preg_replace('/[^\d]/', '', $phone_number);

            // Prepare parameters
            $parameters = [
                'to' => '+' . $normalized_phone_number,
                'body' => $sms_body,
                'options' => $options,
            ];

            // Add sender_id if provided
            if (!empty($sender_id)) {
                $parameters['from'] = $sender_id;
            }

            try {
                // Initialize cURL request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                // Set headers
                $headers = [
                    "Authorization: Basic " . base64_encode("$callr_api_login:$callr_api_password"),
                    "Content-Type: application/json",
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Execute the request
                $result = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);

                // Handle cURL errors
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }

                // Decode the response
                $result = json_decode($result, true);

                // Check for a valid response
                if (is_array($result) && isset($result['status'])) {
                    if ($result['status'] === 'error') {
                        SMSHistory::where('id', $smsHistoryId)->update([
                            'status' => 'failed',
                            'error' => $result['data']['message'] ?? 'Unknown error occurred',
                        ]);
                    } else {
                        SMSHistory::where('id', $smsHistoryId)->update([
                            'status' => 'sent',
                            'send_at' => Carbon::now(),
                        ]);
                    }
                } else {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => 'Invalid request response',
                    ]);
                }
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage() ?: 'Unknown error occurred',
                    'send_at' => Carbon::now(),
                ]);
                return false;
            }
        } elseif ($provider == 'CMCOM') { //Has Callback
            $url = sms_setting('cm_api_link') != '' ? sms_setting('cm_api_link') : 'https://gw.cmtelecom.com/v1.0/message';
            $api_token = sms_setting('cm_product_token');
            $sender_id = sms_setting('cm_sender_id') ?? null;
            $random_data = Str::random(2) . now()->format('YmdHis');;
            $parameters = [
                'messages' => [
                    'authentication' => [
                        'productToken' => $api_token,
                    ],
                    'msg'            => [
                        [
                            'from'     => $sender_id,
                            'body'    => [
                                'content' => $sms_body,
                                'type'    => 'auto',
                            ],
                            'minimumNumberOfMessageParts' => 1,
                            'maximumNumberOfMessageParts' => 8,
                            'to'       => [
                                [
                                    'number' => '+' . $phone_number,
                                ],
                            ],
                            'allowedChannels'  => [
                                'SMS',
                            ],
                            'reference' => $random_data,
                        ],
                    ],
                ],
            ];
            $headers = [
                'Content-Type: application/json;charset=UTF-8',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $err,
                ]);
                return false;
            }

            $responseData = json_decode($response, true);

            if ($responseData['errorCode'] == 0) {
                // Extract message ID from the response, if available
                $messageId = $responseData['messages'][0]['messageId'] ?? null;
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'sent',
                    'message_id' => $messageId,
                    'send_at' => Carbon::now(),
                ]);
                return true;
            } else {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $responseData['details'] ?? 'Unknown error occurred',
                    'send_at' => Carbon::now(),
                ]);
                return false;
            }
        } elseif ($provider == 'AfricasTalking') { //Has Callback
            $url = sms_setting('africastalking_api_link') != '' ? sms_setting('africastalking_api_link') : 'https://api.africastalking.com/version1/messaging';
            $username = sms_setting('africastalking_username');
            $api_key = sms_setting('africastalking_api_key') ?? null;
            $sender_id = sms_setting('africastalking_sender_id') ?? null;
            $parameters = [
                'username' => $username,
                'message'  => $sms_body,
                'to'       => $phone_number,
                'from'     => $sender_id,
            ];

            try {
                $headers = [
                    'apiKey:' . $api_key,
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                ];

                // Initialize cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POST, count($parameters));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Execute request and capture response
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);

                // Handle errors in the cURL request
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }
                // Decode the response
                $responseData = json_decode($response, true);
                if (isset($responseData['SMSMessageData']['Recipients'][0])) {
                    $recipient = $responseData['SMSMessageData']['Recipients'][0]; // Get only the first recipient
                    // Update status based on response
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => $recipient['status'] === 'Success' ? 'sent' : 'failed',
                        'message_id' => $recipient['messageId'] ?? null,
                        'send_at' => Carbon::now(),
                        'error' => $recipient['status'] !== 'Success' ? $recipient['status'] : null,
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }

            return false;
        } elseif ($provider == '1s2u') { //Has Callback
            $url = sms_setting('1s2u_api_link') != '' ? sms_setting('1s2u_api_link') : 'https://api.1s2u.io/bulksms';
            $username = sms_setting('1s2u_username');
            $password = sms_setting('1s2u_password') ?? null;
            $sender_id = sms_setting('1s2u_sender_id') ?? null;

            $parameters = [
                "username" => $username,
                "password" => $password,
                "mno"      => $phone_number,
                "msg"      => $sms_body,
                "sid"      => $sender_id,
                "mt"       => 0,
                "fl"       => 0,
            ];
            // Build the query string
            $url = $url . '?' . http_build_query($parameters);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                // Execute cURL request
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                // Handle cURL error
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }
                $responseData = json_decode($response, true);
                if (isset($responseData)) {
                    $statusCode = $responseData['status'] ?? null;
                    switch ($statusCode) {
                        case '0000':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Service is temporarily unavailable or down.',
                            ]);
                            break;

                        case '000':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Please ensure all required parameters are filled in correctly.',
                            ]);
                            break;

                        case '00':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid username or password or trial account expired.',
                            ]);
                            break;

                        case '0020':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Insufficient account credits to perform the operation.',
                            ]);
                            break;

                        case '0030':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid sender name.',
                            ]);
                            break;

                        case '0041':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid mobile number.',
                            ]);
                            break;

                        case '0042':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Network is not supported/activated.',
                            ]);
                            break;

                        case '0050':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid SMS text message.',
                            ]);
                            break;

                        case '0051':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid message type.',
                            ]);
                            break;

                        case '0060':
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Invalid submission limit.',
                            ]);
                            break;

                        default:
                            SMSHistory::where('id', $smsHistoryId)->update([
                                'status' => 'failed',
                                'error' => 'Unknown error occurred.',
                            ]);
                            break;
                    }

                    // If sending was successful, update status
                    if (isset($responseData['message_id'])) {
                        SMSHistory::where('id', $smsHistoryId)->update([
                            'status' => 'sent',
                            'message_id' => $responseData['message_id'], // Capture message_id from response
                            'send_at' => Carbon::now(),
                        ]);
                        return true;
                    }
                }
            } catch (\Exception $ex) {
                // Handle exceptions
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $ex->getMessage(),
                ]);
                return false;
            }
        } elseif ($provider == 'KeccelSMS') { //Has Callback
            $url = sms_setting('keccelsms_api_link') != '' ? sms_setting('keccelsms_api_link') : 'http://161.97.92.251:22099/message';
            $application_id = sms_setting('keccelsms_application_id');
            $password = sms_setting('keccelsms_password') ?? null;
            $sender_id = sms_setting('keccelsms_sender_id') ?? null;

            $parameters = [
                'pass'   => $password,
                'id'     => $application_id,
                'from'   => $sender_id,
                'to'     => $phone_number,
                'text'   => $sms_body,
                'dlrreq' => 1,
            ];
            $parameters = http_build_query($parameters);
            try {
                $url = $url . '?user=' . $application_id . '&' . $parameters;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }
                if (is_numeric($response)) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $response,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                } else {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => 'Invalid gateway information',
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        } elseif ($provider == 'GatewayAPI') { //Has Callback
            $url = sms_setting('gatewayapi_api_link') != '' ? sms_setting('gatewayapi_api_link') : 'https://gatewayapi.com/rest/mtsms';
            $api_token = sms_setting('gatewayapi_api_token');
            $sender = sms_setting('gatewayapi_sender_id') ?? null;
            $fields = [
                'message'      => $sms_body,
                'sender'       => $sender,
                'callback_url' => route('sms.dlr.gatewayapi'),
                'max_parts'    => 9,
                'recipients'   => [
                    [
                        'msisdn' => $phone_number,
                    ],
                ],
            ];

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Consider enabling in production
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_USERPWD, $api_token . ":");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);

                if ($err) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $err,
                    ]);
                    return false;
                }

                $responseData = json_decode($response, true);
                if (isset($responseData['ids']) && count($responseData['ids']) > 0) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'sent',
                        'message_id' => $responseData['ids'][0],
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif ($provider == 'Custom') {
            try {
                $requestData = [];
                $usernameParam = sms_setting('custom_username_param');
                $usernameValue = sms_setting('custom_username_value');

                // Handle authorization based on selected type
                if (sms_setting('custom_authorization') == 'no_auth') {
                    $requestData[$usernameParam] = $usernameValue;
                }

                // Handle password if applicable
                $passwordValue = null;
                if (sms_setting('custom_password_param_type')) {
                    $passwordParam = sms_setting('custom_password_param');
                    $passwordValue = sms_setting('custom_password_value');
                    if (sms_setting('custom_authorization') == 'no_auth') {
                        $requestData[$passwordParam] = $passwordValue;
                    }
                }

                // Handle action parameter
                if (sms_setting('custom_action_param_type')) {
                    $actionParam = sms_setting('custom_action_param');
                    $requestData[$actionParam] = sms_setting('custom_action_value');
                }

                // Handle sender ID
                if (sms_setting('sender_id_param_type')) {
                    $sourceParam = sms_setting('custom_sender_id_param');
                    $sourceValue = sms_setting('custom_sender_id_value') ?: sms_setting('custom_sender_id_value');
                    $requestData[$sourceParam] = $sourceValue;
                }

                // Set destination and message
                $destinationParam = sms_setting('custom_destination_number_param');
                $requestData[$destinationParam] = $phone_number;
                $messageParam = sms_setting('custom_message_param');
                $requestData[$messageParam] = $sms_body;

                // Handle custom fields
                for ($i = 1; $i <= 4; $i++) {
                    if (sms_setting("custom_field_{$i}_param_type")) {
                        $customParam = sms_setting("custom_field_{$i}_param");
                        $customValue = sms_setting("custom_field_{$i}_value");
                        $requestData[$customParam] = $customValue;
                    }
                }

                // Prepare parameters based on JSON setting
                $parameters = sms_setting('custom_is_json') ? json_encode($requestData) : http_build_query($requestData);

                // Initialize cURL
                $ch = curl_init();
                $apiUrl = sms_setting('custom_api_link');
                if (sms_setting('custom_method') == 'get') {
                    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . $parameters);
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                } else {
                    curl_setopt($ch, CURLOPT_URL, $apiUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                }

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // SSL Verification
                if (sms_setting('custom_ssl_certificate_verify')) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                }

                // Set headers
                $headers = [];
                if (sms_setting('custom_content_type') != 'none') {
                    $headers[] = "Content-Type: " . sms_setting('custom_content_type');
                }
                if (sms_setting('custom_content_type_accept') != 'none') {
                    $headers[] = "Accept: " . sms_setting('custom_content_type_accept');
                }
                if (sms_setting('custom_character_encoding') != 'none') {
                    $headers[] = "charset=" . sms_setting('custom_character_encoding');
                }
                if (sms_setting('custom_authorization') == 'bearer_token') {
                    $headers[] = "Authorization: Bearer " . $usernameValue;
                }
                if (sms_setting('custom_authorization') == 'basic_auth') {
                    $headers[] = "Authorization: Basic " . base64_encode("$usernameValue:$passwordValue");
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }

                // Execute cURL
                $response = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);

                // Error handling
                if ($error) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'failed',
                        'error' => $error,
                    ]);
                    return false;
                }

                // Check response for success
                $responseData = json_decode($response, true);
                if (substr_count(strtolower($response), strtolower(sms_setting('custom_success_value'))) === 1) {
                    SMSHistory::where('id', $smsHistoryId)->update([
                        'status' => 'delivered',
                        'message_id' => $response,
                        'send_at' => Carbon::now(),
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                SMSHistory::where('id', $smsHistoryId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            return false;
        }
    }

    public function checkMessageStatusAndUpdate($messageId, $provider = '')
    {
        $provider = $provider != '' ? $provider : sms_setting('active_sms_provider');
        $smsCampaignDetail = \App\Models\SMSHistory::find($messageId);
        if (!$smsCampaignDetail) {
            return 'Message not found';
        }
        if ($provider == 'Fast2SMS') {
            $fields = [
                'request_id' => $smsCampaignDetail->message_id, // Assuming message_id holds the request id
            ];
            $auth_key = sms_setting('fast_2_auth_key');
            $curl     = \curl_init();
            \curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://www.fast2sms.com/dev/status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => json_encode($fields),
                CURLOPT_HTTPHEADER     => [
                    "authorization: $auth_key",
                    'content-type: application/json',
                ],
            ]);
            $response = \curl_exec($curl);
            \curl_close($curl);
            if (str_contains($response, 'DELIVERED')) {
                $smsCampaignDetail->status = 'delivered';
            }
            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'REVESystems') {
            // REVESystems status check logic
            $url          = "http://apismpp.revesms.com/getstatus";
            $params       = [
                'apikey'         => sms_setting('reve_sms_api_key'),
                'secretkey'      => sms_setting('reve_secret_key'),
                'messageid' => $smsCampaignDetail->message_id,
            ];


            $ch = \curl_init();
            $data = http_build_query($params);
            $getUrl = $url . '?' . $data;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $getUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 80);
            $result = \curl_exec($ch);

            curl_close($ch);
            $result = json_decode($result);
            if (in_array($result->Status, ['109', '108', '114', '101', '-42', '1'])) {
                $smsCampaignDetail->status = 'failed';
                switch ($result->Status) {
                    case '109':
                        $smsCampaignDetail->error = 'API key not provided';
                        break;
                    case '108':
                        $smsCampaignDetail->error = 'Wrong password or not provided';
                        break;
                    case '114':
                        $smsCampaignDetail->error = 'Message ID not provided or already queried';
                        break;
                    case '101':
                        $smsCampaignDetail->error = 'Internal server error';
                        break;
                    case '-42':
                        $smsCampaignDetail->error = 'Authorization failed';
                        break;
                    case '1':
                        $smsCampaignDetail->error = 'Request failed';
                        break;
                    default:
                        $smsCampaignDetail->error = 'Unknown error';
                }
            } else if ($result->Status == '2') {
                $smsCampaignDetail->status = 'pending';
            } else if ($result->Status == '4') {
                $deliveryTimestamp = (int) $result->{"Delivery Time"};
                $deliveryDate = Carbon::createFromTimestampMs($deliveryTimestamp);
                $smsCampaignDetail->status = 'delivered';
                $smsCampaignDetail->send_at = $deliveryDate;
                $smsCampaignDetail->delivered_at = $deliveryDate;
            } else if ($result->Status == '0') {
                $smsCampaignDetail->status = 'delivered';
                $deliveryTimestamp = (int) $result->{"Delivery Time"};
                $deliveryDate = Carbon::createFromTimestampMs($deliveryTimestamp);
                $smsCampaignDetail->delivered_at = $deliveryDate;
            }
            $smsCampaignDetail->save();
            $smsCampaign = $smsCampaignDetail->campaign;
            $previousStatus = $smsCampaignDetail->getOriginal('status');
            if ($smsCampaignDetail->status != $previousStatus) {
                if ($smsCampaignDetail->status == 'sent') {
                    $smsCampaign->total_sent += 1;
                } else if ($smsCampaignDetail->status == 'delivered') {
                    $smsCampaign->total_delivered += 1;
                } else if ($smsCampaignDetail->status == 'failed') {
                    $smsCampaign->total_failed += 1;
                }
                if ($previousStatus == 'sent') {
                    $smsCampaign->total_sent -= 1;
                } else if ($previousStatus == 'delivered') {
                    $smsCampaign->total_delivered -= 1;
                } else if ($previousStatus == 'failed') {
                    $smsCampaign->total_failed -= 1;
                }
                $smsCampaign->save();
            }
            return true;
        } elseif ($provider == 'Nexmo') {
            // Nexmo status check
            try {
                $basic  = new \Vonage\Client\Credentials\Basic(sms_setting('nexmo_sms_key'), sms_setting('nexmo_sms_secret_key'));
                $client = new \Vonage\Client($basic);
                $response = $client->search()->getMessage($smsCampaignDetail->message_id);
                if ($response['status'] == 0) {
                    $smsCampaignDetail->status = 'delivered';
                } else {
                    $smsCampaignDetail->status = 'failed';
                }
                $smsCampaignDetail->save();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } elseif ($provider == 'SSLWireless') {
            $data = [
                'api_token' => sms_setting('ssl_sms_api_token'),
                'sid'       => sms_setting('ssl_sms_sid'),
                'csms_id'   => $smsCampaignDetail->message_id,
            ];

            $ch = \curl_init();
            $url = sms_setting('ssl_sms_status_url');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $response = \curl_exec($ch);
            curl_close($ch);

            if (str_contains($response, 'DELIVERED')) {
                $smsCampaignDetail->status = 'delivered';
            } else {
                $smsCampaignDetail->status = 'failed';
            }

            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'SMSala') {
            $url = "https://api.smsala.com/api/GetDeliveryStatus";
            $data = [
                'api_id'       => sms_setting('sms_ala_auth_id'),
                'api_password' => sms_setting('sms_ala_password'),
                'message_id'   => $smsCampaignDetail->message_id,
            ];
            $ch = \curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $response = \curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);

            if (str_contains($response, 'Delivered')) {
                $smsCampaignDetail->status = 'delivered';
                $smsCampaignDetail->delivered_at = Carbon::now();
            } elseif (str_contains($response, 'Failed')) {
                $smsCampaignDetail->status = 'failed';
            } elseif (str_contains($response, 'Rejected')) {
                $smsCampaignDetail->status = 'failed';
            }
            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'BudgetSMS') {
            $url = 'https://api.budgetsms.net/checksms';
            $username = sms_setting('budgetsms_username');
            $userid = sms_setting('budgetsms_user_id');
            $handle = sms_setting('budgetsms_handle');
            $smsid = $smsCampaignDetail->message_id;
            $fields = [
                'username' => $username,
                'userid' => $userid,
                'handle' => $handle,
                'smsid' => $smsid,
            ];
            $encodedFields = http_build_query($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedFields);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
            } else {
                if (strpos($response, 'OK') !== false) {
                    $dlrStatus = (int) str_replace('OK ', '', $response);
                    $dlrStatusMap = [
                        0 => 'sent_no_status',
                        1 => 'delivered',
                        2 => 'not_sent',
                        3 => 'delivery_failed',
                        4 => 'sent',
                        5 => 'expired',
                        6 => 'invalid_address',
                        7 => 'smsc_error',
                        8 => 'not_allowed',
                        11 => 'status_unknown_24h',
                        12 => 'status_unknown_smsc_code',
                        13 => 'status_unknown_72h'
                    ];
                    $statusText = $dlrStatusMap[$dlrStatus] ?? 'unknown_status';
                    if (in_array($statusText, ['delivered'])) {
                        $smsCampaignDetail->status = 'delivered';
                        $smsCampaignDetail->delivered_at = Carbon::now();
                    } elseif (in_array($statusText, ['not_sent', 'delivery_failed', 'expired', 'invalid_address', 'smsc_error', 'not_allowed'])) {
                        $smsCampaignDetail->status = 'failed';
                        $smsCampaignDetail->delivered_at = Carbon::now();
                    }
                }
            }
            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'NobelSMS') {
            $url = 'https://api.nobelsms.com/checkDelivery';
            $username = sms_setting('nobel_sms_username');
            $password = sms_setting('nobel_sms_password');
            $messageId = $smsCampaignDetail->message_id;
            $params = [
                'username' => $username,
                'password' => $password,
                'smsid'    => $messageId
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            if (!$responseData || !isset($responseData['status_code'])) {
                Log::error('Invalid NobelSMS API response');
                return false;
            }
            switch ($responseData['status_code']) {
                case '1':
                    $smsCampaignDetail->status = 'delivered';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case '2':
                case '3':
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case '0':
                case '4':
                    $smsCampaignDetail->status = 'sent';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case '5':
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                default:
                    break;
            }
            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'Msg91') {
            $url = 'https://api.msg91.com/api/v5/delivery/';
            $authKey = sms_setting('msg91_auth_key');
            $smsId = $smsCampaignDetail->message_id;
            $requestUrl = $url . $smsId . '?authkey=' . $authKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                Log::error('Msg91 Delivery Check Failed: ' . $err);
                return false;
            }
            // Decode the API response (assuming it's JSON)
            $responseData = json_decode($response, true);
            if (!$responseData || !isset($responseData['status'])) {
                Log::error('Invalid Msg91 API response');
                return false;
            }
            // Map Msg91 delivery statuses to your application
            switch ($responseData['status']) {
                case 'DELIVRD':  // Delivered
                    $smsCampaignDetail->status = 'delivered';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;

                case 'UNDELIV':  // Delivery failed
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;

                case 'SENT':  // Sent, no confirmation of delivery yet
                    $smsCampaignDetail->status = 'sent';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;

                case 'EXPIRED':  // Expired
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;

                case 'REJECTED':  // Rejected by the SMSC
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                default:  // Unknown status

                    break;
            }
            $smsCampaignDetail->save();
            return true;
        } elseif ($provider == 'ValueFirst') {
            $url = 'https://api.myvaluefirst.com/smpp/sms/status';
            $username = sms_setting('valuefirst_username');
            $password = sms_setting('valuefirst_password');
            $smsId = $smsCampaignDetail->message_id;
            $fields = [
                'username' => $username,
                'password' => $password,
                'smsid'    => $smsId
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                Log::error('ValueFirst DLR Check Failed: ' . $err);
                return false;
            }
            $responseData = json_decode($response, true);
            if (!$responseData || !isset($responseData['status'])) {
                Log::error('Invalid ValueFirst API response');
                return false;
            }
            switch ($responseData['status']) {
                case 'DELIVERED':
                    $smsCampaignDetail->status = 'delivered';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case 'FAILED':
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case 'PENDING':
                    $smsCampaignDetail->status = 'pending';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case 'EXPIRED':
                    $smsCampaignDetail->status = 'failed';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                case 'REJECTED':
                    $smsCampaignDetail->status = 'rejected';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
                default:
                    $smsCampaignDetail->status = 'unknown';
                    $smsCampaignDetail->delivered_at = Carbon::now();
                    break;
            }
            return true;
        }
        

        return 'Provider not supported for status check.';
    }


    public function getToken()
    {
        $curl     = \curl_init();

        \curl_setopt_array($curl, [
            CURLOPT_URL            => '52.30.114.86:8080/mimosms/v1/user/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => '{
                "username": "' . sms_setting('mimo_username') . '",
                "password": "' . sms_setting('mimo_sms_password') . '"
            }',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
            ],
        ]);

        $response = \curl_exec($curl);

        \curl_close($curl);

        return json_decode($response)->token;
    }

    public function sendMessages($phone_number, $sms_body, $token): bool
    {
        $curl     = \curl_init();

        $fields   = [
            'sender'     => sms_setting('mimo_sms_sender_id'),
            'text'       => $sms_body,
            'recipients' => $phone_number,
        ];

        \curl_setopt_array($curl, [
            CURLOPT_URL            => '52.30.114.86:8080/mimosms/v1/message/send?token=' . $token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($fields),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
            ],
        ]);

        $response = \curl_exec($curl);

        \curl_close($curl);

        return true;
    }

    public function logout($token): bool
    {
        $curl     = \curl_init();

        \curl_setopt_array($curl, [
            CURLOPT_URL            => '52.30.114.86:8080/mimosms/v1/user/logout?token=' . $token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ]);

        $response = \curl_exec($curl);

        \curl_close($curl);

        return true;
    }

    public function sendSMS($phone_number, $key, $otp): bool|string|null
    {
        $sms_templates = app('sms_templates');
        $sms_template  = $sms_templates->where('tab_key', $key)->first();
        $sms_body      = str_replace('{otp}', $otp, @$sms_template->sms_body);

        return $this->send($phone_number, $sms_body, $sms_template->template_id);
    }
    private function getEasySendSMSErrorMessage(int $errorCode): string
    {
        // Map error codes to corresponding messages
        $errorMessages = [
            1001 => 'Invalid URL. One of the parameters was not provided or left blank.',
            1002 => 'Invalid username or password parameter.',
            1003 => 'Invalid type parameter.',
            1004 => 'Invalid message content.',
            1005 => 'Invalid mobile number.',
            1006 => 'Invalid Sender name.',
            1007 => 'Insufficient credit.',
            1008 => 'Internal server error.',
            1009 => 'Service not available.',
            // Default fallback for unknown errors
            'default' => 'Unknown error occurred.'
        ];

        // Return the corresponding message or a default error message
        return $errorMessages[$errorCode] ?? $errorMessages['default'];
    }
    // Error message handler based on error codes
    private function getBudgetSMSErrorMessage($errorCode)
    {
        $errorMessages = [
            1001 => 'Not enough credits to send messages',
            1002 => 'Identification failed. Wrong credentials',
            1003 => 'Account not active, contact BudgetSMS',
            1004 => 'This IP address is not added to this account. No access to the API',
            1005 => 'No handle provided',
            1006 => 'No UserID provided',
            1007 => 'No Username provided',
            2001 => 'SMS message text is empty',
            2002 => 'SMS numeric senderid can be max. 16 numbers',
            2003 => 'SMS alphanumeric sender can be max. 11 characters',
            2004 => 'SMS senderid is empty or invalid',
            2005 => 'Destination number is too short',
            2006 => 'Destination is not numeric',
            2007 => 'Destination is empty',
            2008 => 'SMS text is not OK (check encoding?)',
            2009 => 'Parameter issue (check all mandatory parameters, encoding, etc.)',
            2010 => 'Destination number is invalidly formatted',
            2011 => 'Destination is invalid',
            2012 => 'SMS message text is too long',
            2013 => 'SMS message is invalid',
            2014 => 'SMS CustomID is used before',
            2015 => 'Charset problem',
            2016 => 'Invalid UTF-8 encoding',
            2017 => 'Invalid SMSid',
            3001 => 'No route to destination. Contact BudgetSMS for possible solutions',
            3002 => 'No routes are setup. Contact BudgetSMS for a route setup',
            3003 => 'Invalid destination. Check international mobile number formatting',
            4001 => 'System error, related to customID',
            4002 => 'System error, temporary issue. Try resubmitting in 2 to 3 minutes',
            4003 => 'System error, temporary issue.',
            4004 => 'System error, temporary issue. Contact BudgetSMS',
            4005 => 'System error, permanent',
            4006 => 'Gateway not reachable',
            4007 => 'System error, contact BudgetSMS',
            5001 => 'Send error, Contact BudgetSMS with the send details',
            5002 => 'Wrong SMS type',
            5003 => 'Wrong operator',
            6001 => 'Unknown error',
            7001 => 'No HLR provider present, Contact BudgetSMS.',
            7002 => 'Unexpected results from HLR provider',
            7003 => 'Bad number format',
            7901 => 'Unexpected error. Contact BudgetSMS',
            7902 => 'HLR provider error. Contact BudgetSMS',
            7903 => 'HLR provider error. Contact BudgetSMS',
        ];

        return $errorMessages[$errorCode] ?? 'An unknown error occurred';
    }

    // Helper function to clean the mobile number
    public function cleanMobileNumber($mobile)
    {
        return preg_replace('/[^\d]/', '', $mobile);
    }
}
