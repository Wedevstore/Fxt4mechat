<?php
namespace App\Traits;
use App\Models\Client;
use App\Enums\StatusEnum;
use App\Traits\CommonTrait;
use App\Enums\MessageStatusEnum;
use App\Models\Contact;
use App\Models\Device;
use App\Models\WebTemplate;
use App\Services\Web\DeviceCreditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Netflie\WhatsAppCloudApi\Message\Template\Component;

trait WhatsAppWebTrait
{
    use SendNotification, CommonTrait;

    public $facebook_api = 'https://graph.facebook.com/v19.0/';

    public function sendWhatsAppWebCampaignSingleContactMessage($message)
    {
        $client = Client::active()->with('webSetting')->find($message->client_id);

        if (!$client) {
            $message->error = "Client not found: {$message->client_id}";
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        try {

            $template = WebTemplate::where('id', $message->web_template_id)->first();

            if (!$template) {
                $message->error = 'Template is empty';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            // Contact must be active
            if (!$message->contact || $message->contact->status != 1) {
                $message->error = 'Contact inactive';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            $contact  = $message->contact;
            $phone    = $contact->phone;

            // Get last active WhatsApp device
            $device = Device::where('id', $contact->device_id)->first();

            if (is_null($device)) {

                // Re-fetch latest client
                $client = Client::active()->with('webSetting')->find(Auth::id());
                $contact = Contact::find($contact->id);

                if ($contact && $contact->device_id) {
                    $device = Device::where('id', $contact->device_id)
                        ->where('client_id', $client->id)
                        ->first();

                    if (is_null($device)) {
                        $message->error = 'Selected device not found.';
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }

                } else {

                    // Get last active device for chat
                    $device = Device::where('client_id', auth()->user()->client->id)
                        ->where('active_for_chat', 1)
                        ->orderByDesc('active_for_chat_time')
                        ->orderByDesc('updated_at')
                        ->first();

                    if (is_null($device)) {
                        $message->error = 'Please select a device first.';
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }
                }
            }

            // Check WhatsApp session
            if (empty($device->whatsapp_session)) {
                Log::error("No active WhatsApp session for device: {$device->id}");

                $message->error = 'No active WhatsApp session found.';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            // Check device status
            if ($device->status !== 'connected') {
                Log::warning("Device {$device->id} is not connected. Current: {$device->status}");

                $message->error = 'Device is not connected to WhatsApp.';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            // Assign session
            $activeWhatsappSession = $device->whatsapp_session;


            $apiUrl = 'https://app.rapiwa.com/api/send-message';


            // Base payload
            $postData = [
                'number' => $phone,
                'message_type' => $message->message_type,
            ];

            // Detect message type
            switch ($message->message_type) {

                case 'text':
                    $postData['message'] = $message->value ?? $template->message;
                    break;

                case 'image':
                case 'video':
                case 'audio':
                case 'document':
                    $headerField = "header_{$message->message_type}";
                    $postData['media_url'] = $message->$headerField ?? $template->media_url;
                    $postData['mimetype']  = $template->mimetype ?? null;
                    $postData['message']   = $message->caption ?? $template->message ?? '';

                    if ($message->message_type === 'document') {
                        $postData['file_name'] = $template->file_name ?? basename($postData['media_url']);
                    }
                    break;

                case 'location':
                    if (!empty($message->header_location)) {
                        $location = json_decode($message->header_location, true);

                        $postData['latitude']  = $location['latitude'] ?? $template->latitude ?? 0;
                        $postData['longitude'] = $location['longitude'] ?? $template->longitude ?? 0;

                    } else {
                        $message->error = "Location data missing";
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }
                    break;

                case 'interactive_button':

                    $decodedButtons = json_decode($message->buttons, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $message->error = 'Invalid buttons JSON';
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }

                    $postData['message_type'] = 'interactive';
                    $postData['message'] = $message->value ?? $template->message;
                    $postData['buttons'] = [];

                    foreach ($decodedButtons as $button) {
                        $postData['buttons'][] = [
                            'buttonId' => $button['id'],
                            'displayText' => $button['text']
                        ];
                    }
                    break;

                default:
                    $message->error = "Unsupported message type: {$message->message_type}";
                    $message->status = MessageStatusEnum::FAILED;
                    $message->save();
                    return false;
            }

            // Send Request
            if (!isDemoMode()) {
                if (
                    setting('whatsapp_web_access') === 'admin' &&
                    optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
                ) {

                    $device = Device::with('credit')
                        ->where('whatsapp_session', $activeWhatsappSession)
                        ->first();

                    if (!$device) {
                        Log::error('Device not found for WhatsApp session', [
                            'session' => $activeWhatsappSession
                        ]);
                        return false;
                    }

                    if ($device->availableCredits() <= 0) {
                        Log::warning('Insufficient device credits', [
                            'device_id' => $device->id,
                            'session' => $activeWhatsappSession
                        ]);

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = 'Insufficient credits';
                        $message->save();

                        return false;
                    }

                    /** SEND MESSAGE */
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);

                    if ($response->successful()) {

                        $data = $response->json();

                        $message->status = MessageStatusEnum::DELIVERED;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }

                        $message->save();

                        /** DEDUCT CREDIT AFTER SUCCESS */
                        try {
                            app(DeviceCreditService::class)->deduct(
                                device: $device,
                                messageType: $message->message_type,
                                reference: $message->id
                            );
                        } catch (\Exception $e) {
                            Log::critical('Credit deduction failed AFTER message sent', [
                                'device_id' => $device->id,
                                'message_id' => $message->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        return true;

                    } else {

                        $body = $response->json();

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = $body['error'] ?? 'Unknown error';
                        $message->save();

                        return false;
                    }
                }
                else{

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);


                    // Handle Response
                    if ($response->successful()) {

                        $data = $response->json();
                        $message->status = MessageStatusEnum::DELIVERED;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }

                    } else {

                        $body = $response->json();
                        $message->error = $body['error'] ?? 'Unknown Error';
                        $message->status = MessageStatusEnum::FAILED;
                    }

                }       
            }

            $message->save();

            return true;


        } catch (\Exception $e) {

            if ($message->campaign) {
                DB::table('campaigns')->where('id', $message->campaign->id)
                    ->update(['status' => StatusEnum::PROCESSED]);
            }

            $message->error = $e->getMessage();
            $message->status = MessageStatusEnum::FAILED;
            $message->save();

            Log::error("Web Campaign Error", ['exception' => $e]);
            return false;
        }
    }

    private function sendWhatsAppWebCampaignMessage($message)
    {
        $client = Client::active()->with('webSetting')->find($message->client_id);

        if (!$client) {
            $message->error = "Client not found: {$message->client_id}";
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        try {
            $template = $message->campaign->webTemplate ?? $message->webTemplate;

            if (!$template) {
                $message->error = 'Template is empty';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            // Contact must be active
            if (!$message->contact || $message->contact->status != 1) {
                $message->error = 'Contact inactive';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            $contact  = $message->contact;
            $phone    = $contact->phone;

            // Get last active WhatsApp session
            $device = Device::where('id', $message->campaign->device_id)->first();
            
            if (is_null($device)) {

                // Find contact & associated device
                $client = Client::active()->with('webSetting')->find(Auth::user()->client_id);
                $contact = Contact::find($contact->id);

                if ($contact && $contact->device_id) {
                    $device = Device::where('id', $contact->device_id)
                        ->where('client_id', $client->id)
                        ->first();

                    if (is_null($device)) {
                        return response()->json([
                            'success' => false,
                            'status'  => false,
                            'error'   => __('Selected device not found.'),
                            'message' => __('Selected device not found.'),
                        ], 404);
                    }

                } else {
                    // Get last active device for chat
                    $device = Device::where('client_id', $client->id)
                        ->where('active_for_chat', 1)
                        ->orderByDesc('active_for_chat_time')
                        ->orderByDesc('updated_at')
                        ->first();

                    if (is_null($device)) {
                        return response()->json([
                            'success' => false,
                            'status'  => false,
                            'error'   => __('Please select a device first.'),
                            'message' => __('Please select a device first.'),
                        ], 400);
                    }
                }
            }

            // Check for WhatsApp session validity
            if (empty($device->whatsapp_session)) {
                Log::error("No active WhatsApp session found for device: {$device->id}");
                return response()->json([
                    'success' => false,
                    'status'  => false,
                    'error'   => __('No active WhatsApp session found for this device.'),
                    'message' => __('No active WhatsApp session found for this device.'),
                ], 400);
            }

            // Check device connection status
            if ($device->status !== 'connected') {
                Log::warning("Device {$device->id} is not connected. Current status: {$device->status}");
                return response()->json([
                    'success' => false,
                    'status'  => false,
                    'error'   => __('Device is not connected to WhatsApp.'),
                    'message' => __('Device is not connected to WhatsApp.'),
                ], 400);
            }

            // Assign session
            $activeWhatsappSession = $device->whatsapp_session;

            // Double-check before proceeding
            if (!$device || empty($activeWhatsappSession)) {
                $message->error = "No device found";
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            // Prepare API URL & base payload
            $apiUrl = 'https://app.rapiwa.com/api/send-message';
            $postData = [
                'number' => $phone,
                'message_type' => $message->message_type,
            ];

            // Handle message type
            switch ($message->message_type) {
                case 'text':
                    $postData['message'] = $message->value ?? $template->message;
                    break;

                case 'image':
                case 'video':
                case 'audio':
                case 'document':
                    $headerField = "header_{$message->message_type}";
                    $postData['media_url'] = $message->$headerField ?? $template->media_url;
                    $postData['mimetype']  = $template->mimetype ?? null;
                    $postData['message']   = $message->caption ?? $template->message ?? '';
                    if ($message->message_type === 'document') {
                        $postData['file_name'] = $template->file_name ?? basename($postData['media_url']);
                    }
                    break;

                case 'location':
                    if (!empty($message->header_location)) {
                        $location = json_decode($message->header_location, true);
                        $postData['latitude']  = $location['latitude'] ?? $template->latitude ?? 0;
                        $postData['longitude'] = $location['longitude'] ?? $template->longitude ?? 0;
                    } else {
                        $message->error = "Location data missing";
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }
                    break;

                case 'interactive_button':
                    $decodedButtons = json_decode($message->buttons, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $message->error = 'Invalid buttons JSON';
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }

                    $postData['message_type'] = 'interactive';
                    $postData['message'] = $message->value ?? $template->message;
                    $postData['buttons'] = [];

                    foreach ($decodedButtons as $button) {
                        $postData['buttons'][] = [
                            'buttonId' => $button['id'],
                            'displayText' => $button['text']
                        ];
                    }
                    break;

                default:
                    $message->error = "Unsupported message type: {$message->message_type}";
                    $message->status = MessageStatusEnum::FAILED;
                    $message->save();
                    return false;
            }

            if (!isDemoMode()) {
                if (
                    setting('whatsapp_web_access') === 'admin' &&
                    optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
                ) {

                    $device = Device::with('credit')
                        ->where('whatsapp_session', $activeWhatsappSession)
                        ->first();

                    if (!$device) {
                        Log::error('Device not found for WhatsApp session', [
                            'session' => $activeWhatsappSession
                        ]);
                        return false;
                    }

                    if ($device->availableCredits() <= 0) {
                        Log::warning('Insufficient device credits', [
                            'device_id' => $device->id,
                            'session' => $activeWhatsappSession
                        ]);

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = 'Insufficient credits';
                        $message->save();

                        return false;
                    }

                    /** SEND MESSAGE */
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);

                    if ($response->successful()) {

                        $data = $response->json();

                        $message->status = MessageStatusEnum::DELIVERED;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }

                        $message->save();

                        /** DEDUCT CREDIT AFTER SUCCESS */
                        try {
                            app(DeviceCreditService::class)->deduct(
                                device: $device,
                                messageType: $message->message_type,
                                reference: $message->id
                            );
                        } catch (\Exception $e) {
                            Log::critical('Credit deduction failed AFTER message sent', [
                                'device_id' => $device->id,
                                'message_id' => $message->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        return true;

                    } else {

                        $body = $response->json();

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = $body['error'] ?? 'Unknown error';
                        $message->save();

                        return false;
                    }
                }
                else{
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);

                    if ($response->successful()) {
                        $data = $response->json();
                        $message->status = MessageStatusEnum::DELIVERED;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }
                    } else {
                        $body = $response->json();
                        $message->error = $body['error'] ?? 'Unknown';
                        $message->status = MessageStatusEnum::FAILED;
                    }

                }       
            }

            $message->save();

            if ($message->campaign && $message->campaign->messages()->count() == 1) {
                DB::table('campaigns')->where('id', $message->campaign->id)->update([
                    'status' => StatusEnum::PROCESSED
                ]);
            }

            return true;
        } catch (\Exception $e) {
            if ($message->campaign) {
                DB::table('campaigns')->where('id', $message->campaign->id)->update([
                    'status' => StatusEnum::PROCESSED
                ]);
            }

            $errorMessage = isset(json_decode($e->getMessage(), true)['error']['message'])
                ? json_decode($e->getMessage(), true)['error']['message']
                : $e->getMessage();

            $message->error = $errorMessage;
            $message->status = MessageStatusEnum::FAILED;
            $message->save();

            logError('Throwable: ', $e);
            return false;
        }
    }



    public function sendWhatsAppWebMessage($message, $message_type, $context = null, $session)
    {
        $client = Client::active()->with('webSetting')->find($message->client_id);

        if (!$client) {
            Log::error("Client not found: {$message->client_id}");
            return false;
        }

        try {
            $contact = $message->contact;
            $activeWhatsappSession = $session;
            $phone = $contact->phone;

            // API URL from client web settings
            $apiUrl = 'https://app.rapiwa.com/api/send-message';

            // Base post data
            $postData = [
                'number' => $phone,
                'message_type' => $message_type,
            ];

            // Reply support
            if (!empty($message->context_id) && $context) {
                $postData['quoted_message_id'] = $message->context_id;
                $postData['quoted_message'] = $context->value ?? '';
            }

            // Handle message types
            switch ($message_type) {
                case 'text':
                    if (isDemoMode()) {
                        $message->value = '[DEMO] Hello there!

Welcome to SaleBot – your all-in-one WhatsApp & Telegram marketing automation platform.

Here’s what you can do with SaleBot:
- Send bulk messages and schedule campaigns with ease  
- Build AI-powered chatbots to support customers and boost sales  
- Automate replies, follow-ups, and drip messaging sequences  
- Manage contacts, leads, and audience segments in one place  
- Track results with real-time campaign analytics  

Explore the live demo: https://livedemo.salebot.app  
Admin Login: admin@salebot.app  
Password: 123456

Subscriber Login: admin@salebot.app  
Password: 123456

if you have any question please contevt here: https://wa.me/8801322827799
Start automating conversations today and watch your conversions grow!';
                    }
                    $postData['message'] = $message->value;
                    break;

                case 'image':
                case 'video':
                case 'audio':
                case 'document':
                    $postData['media_url'] = $message->{"header_{$message_type}"};
                    $postData['mimetype'] = $message->mimetype ?? null;
                    $postData['message'] = $message->caption ?? '';
                    if ($message_type === 'document') {
                        $postData['file_name'] = basename($message->header_document);
                    }
                    break;

                case 'location':
                    if (!empty($message->header_location)) {
                        $location = json_decode($message->header_location, true);
                        $postData['latitude'] = $location['latitude'] ?? 0;
                        $postData['longitude'] = $location['longitude'] ?? 0;
                    } else {
                        Log::error("Location data missing for message ID: {$message->id}");
                        return false;
                    }
                    break;

                case 'interactive_button':
                    $decodedButtons = json_decode($message->buttons, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Invalid JSON for buttons', [$message->buttons]);
                        return false;
                    }

                    $postData['message_type'] = 'interactive';
                    $postData['message'] = $message->value;
                    $postData['buttons'] = [];

                    foreach ($decodedButtons as $button) {
                        $postData['buttons'][] = [
                            'buttonId' => $button['id'],
                            'displayText' => $button['text']
                        ];
                    }
                    break;

                default:
                    Log::error('Unsupported message type', [$message_type]);
                    return false;
            }

            // Send HTTP request with Authorization header
            if (!isDemoMode()) {
                if (
                    setting('whatsapp_web_access') === 'admin' &&
                    optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
                ) {

                    $device = Device::with('credit')
                        ->where('whatsapp_session', $session)
                        ->first();

                    if (!$device) {
                        Log::error('Device not found for WhatsApp session', [
                            'session' => $session
                        ]);
                        return false;
                    }

                    if ($device->availableCredits() <= 0) {
                        Log::warning('Insufficient device credits', [
                            'device_id' => $device->id,
                            'session' => $session
                        ]);

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = 'Insufficient credits';
                        $message->save();

                        return false;
                    }

                    /** SEND MESSAGE */
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);

                    if ($response->successful()) {

                        $data = $response->json();

                        $message->status = MessageStatusEnum::SENT;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }

                        $message->save();

                        /** DEDUCT CREDIT AFTER SUCCESS */
                        try {
                            app(DeviceCreditService::class)->deduct(
                                device: $device,
                                messageType: $message_type,
                                reference: $message->id
                            );
                        } catch (\Exception $e) {
                            Log::critical('Credit deduction failed AFTER message sent', [
                                'device_id' => $device->id,
                                'message_id' => $message->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        return true;

                    } else {

                        $body = $response->json();

                        $message->status = MessageStatusEnum::FAILED;
                        $message->error  = $body['error'] ?? 'Unknown error';
                        $message->save();

                        return false;
                    }
                }
                else{
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $activeWhatsappSession,
                    ])->post($apiUrl, $postData);

                    if ($response->successful()) {
                        $data = $response->json();
                        $message->status = MessageStatusEnum::SENT;

                        if (!empty($data['message_id'])) {
                            $message->message_id = $data['message_id'];
                        }         
                    } else {
                        $body = $response->json();
                        $message->error = $body['error'] ?? 'Unknown';
                        $message->status = MessageStatusEnum::FAILED;
                    }
                }       
            }

            $message->update();

            // Update conversation status
            $this->conversationUpdate($message->client_id, $message->contact_id);

            return true;

        } catch (\Exception $e) {
            Log::error('sendWhatsAppWebMessage Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $message->error = $e->getMessage();
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }
    }

    
    // public function sendWhatsAppWebMessage($message, $message_type)
    // {
    //     $client = Client::active()->with('webSetting')->find($message->client_id);

    //     try {
    //         $contact = $message->contact;
    //         $phone = $contact->phone;

    //         // Get last active device session
    //         $activeWhatsappSession = Device::where('client_id', $client->id)
    //             ->where('active_for_chat', 1)
    //             ->orderByDesc('active_for_chat_time')
    //             ->value('whatsapp_session');

    //         if (!$activeWhatsappSession) {
    //             throw new \Exception("No active WhatsApp session found for client {$client->id}");
    //         }

    //         // API endpoint
    //         $apiUrl = Auth::user()->client->webSetting->app_id . "/api/send-message";

    //         // dd($apiUrl);

    //         // Headers with Bearer token
    //         $headers = [
    //             'Authorization' => 'Bearer ' . $activeWhatsappSession,
    //             'Content-Type'  => 'application/json',
    //         ];

    //         // Base payload
    //         $postData = [
    //             'number' => $phone,
    //             'message_type' => $message_type,
    //         ];

    //         switch ($message_type) {
    //             case 'text':
    //                 $postData['message'] = $message->value;
    //                 break;

    //             case 'image':
    //             case 'video':
    //             case 'audio':
    //             case 'document':
    //                 $postData['media_url'] = $message->{"header_{$message_type}"};
    //                 $postData['mimetype'] = $message->mimetype ?? null;
    //                 $postData['message'] = $message->caption ?? '';
    //                 if ($message_type === 'document') {
    //                     $postData['file_name'] = basename($message->header_document);
    //                 }
    //                 break;

    //             case 'location':
    //                 $location = json_decode($message->header_location, true);
    //                 if (!empty($location)) {
    //                     $postData['latitude'] = $location['latitude'] ?? 0;
    //                     $postData['longitude'] = $location['longitude'] ?? 0;
    //                 } else {
    //                     return false;
    //                 }
    //                 break;

    //             case 'interactive_button':
    //                 $decodedButtons = json_decode($message->buttons, true);

    //                 if (json_last_error() !== JSON_ERROR_NONE) {
    //                     Log::error('Invalid JSON for buttons', [$message->buttons]);
    //                     return false;
    //                 }

    //                 $postData['message_type'] = 'interactive';
    //                 $postData['message'] = $message->value;
    //                 $postData['buttons'] = [];

    //                 foreach ($decodedButtons as $button) {
    //                     $postData['buttons'][] = [
    //                         'buttonId' => $button['id'],
    //                         'displayText' => $button['text']
    //                     ];
    //                 }
    //                 break;

    //             default:
    //                 Log::error('Unsupported message type', [$message_type]);
    //                 return false;
    //         }

            
    //         $response = Http::withHeaders($headers)->post($apiUrl, $postData);

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             $message->status = MessageStatusEnum::SENT;

    //             if (!empty($data['message_id'])) {
    //                 $message->message_id = $data['message_id'];
    //             }
    //         } else {
    //             $body = $response->json();
    //             $message->error = $body['error'] ?? 'Unknown';
    //             $message->status = MessageStatusEnum::FAILED;
    //         }

    //         $message->update();
    //         $this->conversationUpdate($message->client_id, $message->contact_id);
    //         return true;

    //     } catch (\Exception $e) {
    //         logError('sendWhatsAppMessage Exception: ', $e);
    //         $message->error = $e->getMessage();
    //         $message->status = MessageStatusEnum::FAILED;
    //         $message->save();
    //         return false;
    //     }
    // }


    // Function to ensure proper UTF-8 encoding
    public function ensureUtf8($string) {
        // Convert the string to UTF-8
        $utf8String = mb_convert_encoding($string, 'UTF-8', 'auto');
        // Check if the conversion was successful
        if (mb_detect_encoding($utf8String, 'UTF-8', true) === false) {
            throw new \Exception('Invalid UTF-8 encoding detected.');
        }
        return $utf8String;
    }


    public function handleReceivedMedia($client, $media_id, $fileExtension = '.jpg')
    {
        $storage = setting('default_storage') != '' || setting('default_storage') != null ? setting('default_storage') : 'local';
        $url = $this->facebook_api . $media_id;
        $accessToken = getClientWhatsAppAccessToken($client);
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->get($url);
            $content = json_decode($response->body(), true);
            // Check if the response content is valid
            if (!$content || !isset($content['url'])) {
                Log::error('Invalid response content', ['content' => $content]);
                // throw new \Exception('Invalid response content');
            }
            $responseImage = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->withoutVerifying()->get($content['url']);
            $fileContents = $responseImage->getBody()->getContents();
            if ($fileContents === false) {
                Log::error('Error downloading and storing media');
                // throw new \Exception('Error downloading image');
            }
            if ($storage == 'wasabi') {
                $fileName = "images/media/{$content['id']}{$fileExtension}";
                $path = Storage::disk('wasabi')->put($fileName, $fileContents, 'public');
                return Storage::disk('wasabi')->url($fileName);
            } elseif ($storage == 's3') {
                $fileName = "images/media/{$content['id']}{$fileExtension}";
                $path = Storage::disk('s3')->put($fileName, $fileContents, 'public');
                return Storage::disk('s3')->url($fileName);
            } else {
                $directory = public_path('images/media');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true); // Create the directory if it doesn't exist
                }
                $fileName = "{$content['id']}{$fileExtension}";
                $filePath = "{$directory}/{$fileName}";
                file_put_contents($filePath, $fileContents);
                return asset("public/images/media/{$fileName}");     
            }
        } catch (\Exception $e) {
            logError('Error downloading and storing media: ', $e);
            return null;
        }
    }
}
