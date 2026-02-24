<?php
namespace App\Traits;
use App\Models\Client;
use App\Enums\StatusEnum;
use App\Traits\CommonTrait;
use App\Enums\MessageStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\Button;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\ButtonAction;

trait WhatsAppTrait
{
    use SendNotification, CommonTrait;

    public $facebook_api = 'https://graph.facebook.com/v19.0/';

    private function sendWhatsAppCampaignMessage($message)
    {
        $client = Client::find($message->client_id);

        try {
            $accessToken = getClientWhatsAppAccessToken($client);
            $whatspp_phone_number_id = getClientWhatsAppPhoneID($client);
            $template = $message->campaign->template ?? $message->template;

            if (!$template) {
                $message->error = 'Template is empty';
                $message->status = MessageStatusEnum::FAILED;
                $message->save();
                return false;
            }

            if ($message->contact && $message->contact->status == 1) {
                $whatsapp_cloud_api = new WhatsAppCloudApi([
                    'from_phone_number_id' => $whatspp_phone_number_id,
                    'access_token' => $accessToken,
                ]);

                $components = new Component(
                    json_decode($message->component_header) ?? [],
                    json_decode($message->component_body) ?? [],
                    json_decode($message->component_buttons) ?? []
                );

                $message_api = $whatsapp_cloud_api->sendTemplate(
                    $message->contact->phone,
                    $template->name,
                    $template->language,
                    $components
                );

                $message_body = json_decode($message_api->body(), true);

                if (!empty($message_body['messages'])) {
                    $message->message_id = $message_body['messages'][0]['id'];
                    $message->status = MessageStatusEnum::DELIVERED;
                } else {
                    $message->error = $message_body['error']['message'] ?? 'Unknown';
                    $message->status = MessageStatusEnum::FAILED;
                }
                $message->save();
            }

            if ($message->campaign) {
                $campaign = $message->campaign;
                if ($campaign->messages()->count() == 1) {
                    DB::table('campaigns')->where('id', $campaign->id)->update([
                        'status' => StatusEnum::PROCESSED
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            if ($message->campaign) {
                $campaign = $message->campaign;
                DB::table('campaigns')->where('id', $campaign->id)->update([
                    'status' => StatusEnum::PROCESSED
                ]);
            }
    
            $errorMessage = isset(json_decode($e->getMessage(), true)['error']['message']) ? json_decode($e->getMessage(), true)['error']['message'] : 'Unknown';
            $message->error = $errorMessage;
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            logError('Throwable: ', $e);
            return false;
                
        }
    }


    // private function sendWhatsAppCampaignMessage($message)
    // {
    //     $client = Client::find($message->client_id);
    //     try {
    //         $accessToken             = getClientWhatsAppAccessToken($client);
    //         $whatspp_phone_number_id = getClientWhatsAppPhoneID($client);
    //         $template                = $message->campaign->template ?? $message->template;
    //         if (!empty($template)) {
    //             $contact                 = $message->contact;
    //             if ($message->contact->status == 1) {
    //                 $whatsapp_cloud_api = new WhatsAppCloudApi([
    //                     'from_phone_number_id' => $whatspp_phone_number_id,
    //                     'access_token'         => $accessToken,
    //                 ]);
    //                 $component_header   = json_decode($message->component_header)  ?? [];
    //                 $component_body     = json_decode($message->component_body)    ?? [];
    //                 $component_buttons  = json_decode($message->component_buttons) ?? [];
    //                 $components         = new Component($component_header, $component_body, $component_buttons);
    //                 $message_api        = $whatsapp_cloud_api->sendTemplate($contact->phone, $template->name, $template->language, $components);
    //                 $message_body       = json_decode($message_api->body(), true);
    //                 if (!empty($message_body['messages'])) {
    //                     $message->message_id = $message_body['messages'][0]['id'];
    //                     $message->status     = MessageStatusEnum::SENT;
    //                     $message->update();
    //                 } else {
    //                     $message->error  = isset($message_body['error']) ? $message_body['error']['message'] : 'Unknown';
    //                     $message->status = MessageStatusEnum::FAILED;
    //                     $message->update();
    //                 }
    //             }
    //             if ($message->campaign) {
    //                 $campaign = $message->campaign;
    //                 $campaignMessages = $campaign->messages();
    //                 if ($campaignMessages->count() == 1) {
    //                     DB::table('campaigns')->where('id', $campaign->id)->update([
    //                         'status' => StatusEnum::PROCESSED
    //                     ]);
    //                 }
    //             }
    //             return true;
    //         } else {
    //             $message->error  = 'Template is empty';
    //             $message->status = MessageStatusEnum::FAILED;
    //             $message->update();
    //             return false;
    //         }
    //     } catch (\Exception $e) {
    //         if ($message->campaign) {
    //             $campaign = $message->campaign;
    //             DB::table('campaigns')->where('id', $campaign->id)->update([
    //                 'status' => StatusEnum::PROCESSED
    //             ]);
    //         }
            
    //         $errorMessage = isset(json_decode($e->getMessage(), true)['error']['message']) ? json_decode($e->getMessage(), true)['error']['message'] : 'Unknown';
    //         $message->error = $errorMessage;
    //         $message->status = MessageStatusEnum::FAILED;
    //         $message->save();
    //         logError('Throwable: ', $e);
    //         return false;
    //     }
    // }

    public function sendWhatsAppMessage($message, $message_type)
    {
        $client = Client::active()->find($message->client_id);
        try { 
            $response = [];
            $accessToken = getClientWhatsAppAccessToken($client);
            $whatsapp_phone_number_id = getClientWhatsAppPhoneID($client);
            // Log::error('$whatsapp_phone_number_id',[$whatsapp_phone_number_id]);
            $contact = $message->contact;
            $whatsapp_cloud_api = new WhatsAppCloudApi([ 
                'from_phone_number_id' => $whatsapp_phone_number_id,
                'access_token' => $accessToken,
            ]);
              // Check if context_id is not empty and set reply context
            if (!empty($message->context_id)) {
                $whatsapp_cloud_api->replyTo($message->context_id);
            }
            if ($message_type == 'text') {
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
                $response = $whatsapp_cloud_api->sendTextMessage($contact->phone, $message->value);
            } elseif ($message_type == 'image') {
                $link_id = new LinkID($message->header_image);
                $response = $whatsapp_cloud_api->sendImage($contact->phone, $link_id);
            } elseif ($message_type == 'audio') {
                $link_id = new LinkID($message->header_audio);
                $response = $whatsapp_cloud_api->sendAudio($contact->phone, $link_id);
               
            } elseif ($message_type == 'video') {
                $caption = $message->caption ?? '';
                $link_id = new LinkID($message->header_video);
                $response = $whatsapp_cloud_api->sendVideo($contact->phone, $link_id, $caption);
                
            } elseif ($message_type == 'document') {  
                $document_name = basename($message->header_document);
                $caption = $message->caption ?? '';
                $document_link = $message->header_document;
                $link_id = new LinkID($document_link);
                $response = $whatsapp_cloud_api->sendDocument($contact->phone, $link_id, $document_name, $caption);
            } 

            elseif ($message_type == 'location') {
                $header_location = $message->header_location;
                $response = $whatsapp_cloud_api->sendTextMessage($contact->phone, $header_location);
            } 

            elseif ($message_type == 'interactive_button') {
                $messageResponse = json_decode($message->buttons, true);
            
                // Check for JSON decode errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error:', ['error' => json_last_error_msg()]);
                    $message->error = 'Invalid JSON format for buttons';
                    $message->status = MessageStatusEnum::FAILED;
                    $message->save();
                    return false;
                }
            
                $buttons = [];
                foreach ($messageResponse as $key => $button) {
                    // Ensure the button title is UTF-8 encoded
                    $title = isset($button['text']) ? $button['text'] : '';
                    $title = mb_convert_encoding($title, 'UTF-8', 'auto'); // Ensure UTF-8 encoding
            
                    // Validate button title length
                    if (mb_strlen($title) >= 1 && mb_strlen($title) <= 20) {
                        $buttons[] = new Button($button['id'], $title);
                    } else {
                        if (mb_strlen($title) > 20) {
                            $title = mb_substr($title, 0, 20);
                            $buttons[] = new Button($button['id'], $title);
                        }
                    }
                }
            
                // Create ButtonAction with validated buttons
                $action = new ButtonAction($buttons);

                log::info('whaatsapp button data checkk', [$action]);
            
                // Handle header and footer text
                $header = isset($message->header_text) ? mb_convert_encoding($message->header_text, 'UTF-8', 'auto') : '';
                if (mb_strlen($header) > 60) {
                    $header = mb_substr($header, 0, 57) . '...';
                }
            
                $footer = isset($message->footer_text) ? mb_convert_encoding($message->footer_text, 'UTF-8', 'auto') : '';
                if (mb_strlen($footer) > 60) {
                    $footer = mb_substr($footer, 0, 57) . '...';
                }

                log::info('whatsapp header checck for interactive_button', [$header]);
            
                // Send the buttons using the WhatsApp Cloud API
                $response = $whatsapp_cloud_api->sendButton(
                    $contact->phone,
                    $message->value,
                    $action,
                    $header,
                    $footer
                );
            }
            
            
            $message_body = json_decode($response->body(), true);

            if (!empty($message_body['messages'])) {
                $message->message_id = $message_body['messages'][0]['id'];
                $message->status = MessageStatusEnum::SENT;
            } else {
                $message->error = isset($message_body['error']) ? $message_body['error']['message'] : 'Unknown';
                $message->status = MessageStatusEnum::FAILED;
                // $this->conversationUpdate($message->client_id, $message->contact_id);
                // return true;
            }
            $message->update();
            $this->conversationUpdate($message->client_id, $message->contact_id);
            return true;
        } catch (\Exception $e) {
            logError('sendWhatsAppMessage Exception: ', $e);
            $errorMessage = isset(json_decode($e->getMessage(), true)['error']['message']) ? json_decode($e->getMessage(), true)['error']['message'] : strip_tags($e->getMessage());
            $message->error = $errorMessage;
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }
    }

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
