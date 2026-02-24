<?php

namespace App\Traits;

use App\Enums\MessageStatusEnum;
use App\Enums\StatusEnum;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait MessengerTrait
{
    public $facebook_api = 'https://graph.facebook.com/v19.0/';

    public function handleReceivedMedia($imageUrl)
    {
        try {
            $storage = setting('default_storage') ?? 'local';
    
            // Get file extension
            $fileExtension = $this->getFileExtension($imageUrl);
    
            // Download image with headers (Facebook requires User-Agent)
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', // Fake browser UA
            ])->get($imageUrl);
    
            if (!$response->ok()) {
                throw new \Exception("Failed to download image. Status: " . $response->status());
            }
    
            $fileContents = $response->body();
            $fileName = "images/media/" . time() . '.' . $fileExtension;
    
            if ($storage === 'wasabi') {
                Storage::disk('wasabi')->put($fileName, $fileContents, 'public');
                return Storage::disk('wasabi')->url($fileName);
            } elseif ($storage === 's3') {
                Storage::disk('s3')->put($fileName, $fileContents, 'public');
                return Storage::disk('s3')->url($fileName);
            } else {
                $directory = public_path('images/media');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $filePath = "{$directory}/" . basename($fileName);
                file_put_contents($filePath, $fileContents);
                return asset("public/images/media/" . basename($fileName));
            }
        } catch (\Throwable $th) {
            Log::error('Media Download Failed: ' . $th->getMessage());
            return null;
        }
    }

    private function getFileExtension($url)
    {
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        if (isset($pathInfo['extension'])) {
            return $pathInfo['extension'];
        }
    
        try {
            $headers = get_headers($url, 1);
            $mimeType = $headers["Content-Type"] ?? null;
    
            if ($mimeType) {
                return match ($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg',
                };
            }
        } catch (\Exception $e) {
            Log::error('Failed to get MIME type: ' . $e->getMessage());
        }
    
        return 'jpg';
    }

    private function getSenderName($sender_contact,$client)
    {
        $accessToken = getClientMessengerAccessToken($client);
        $url = $this->facebook_api.$sender_contact.'?picture?height=200&width=200&access_token='.$accessToken;
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->get($url);
           $content = json_decode($response, true);
           return $content;
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }

    
    public function sendMessengerMessage($message, $message_type)
    {
        // Ensure $message is an object
        if (!is_object($message)) {
            Log::error('Invalid $message provided. Expected object, got:', ['message' => $message]);
            return false;
        }

        // Ensure $message has the required properties
        if (!isset($message->client_id) || !isset($message->contact)) {
            Log::error('Invalid $message object. Missing required properties:', ['message' => $message]);
            return false;
        }

        $client = Client::active()->find($message->client_id);

        if (!$client) {
            Log::error('Client not found for client_id:', ['client_id' => $message->client_id]);
            $message->error = 'Client not found';
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        $contact = $message->contact;

        if (!$contact) {
            Log::error('Contact not found for message:', ['message_id' => $message->id]);
            $message->error = 'Contact not found';
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        $accessToken = getClientMessengerAccessToken($client);

        if (empty($accessToken)) {
            Log::error('Access token not found for client:', ['client_id' => $client->id]);
            $message->error = 'Access token not found';
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        $url = $this->facebook_api . "me/messages?access_token=" . $accessToken;

        // Initialize $postData with a default value
        $postData = null;

        // Handle different message types
        switch ($message_type) {
            case 'text':
                $postData = [
                    'recipient' => [
                        'id' => $contact->phone,
                    ],
                    'message' => [
                        'text' => $message->value,
                    ],
                ];
                break;

            case 'image':
                $postData = [
                    'recipient' => [
                        'id' => $contact->phone,
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'image',
                            'payload' => [
                                'url' => $message->header_image,
                            ],
                        ],
                    ],
                ];
                break;

            case 'audio':
                $postData = [
                    'recipient' => [
                        'id' => $contact->phone,
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'audio',
                            'payload' => [
                                'url' => $message->header_audio,
                            ],
                        ],
                    ],
                ];
                break;

            case 'video':
                $postData = [
                    'recipient' => [
                        'id' => $contact->phone,
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'video',
                            'payload' => [
                                'url' => $message->header_video,
                            ],
                        ],
                    ],
                ];
                break;

            case 'document':
                $postData = [
                    'recipient' => [
                        'id' => $contact->phone,
                    ],
                    'message' => [
                        'attachment' => [
                            'type' => 'file',
                            'payload' => [
                                'url' => $message->header_document,
                            ],
                        ],
                    ],
                ];
                break;

                case 'interactive_button':
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
                    foreach ($messageResponse as $button) {
                        // Ensure required keys exist
                        if (!isset($button['id'], $button['text'])) {
                            Log::error('Missing button attributes:', ['button' => $button]);
                            continue;
                        }
    
                        // Convert text to UTF-8 and trim whitespace
                        $title = trim(mb_convert_encoding($button['text'], 'UTF-8', 'auto'));
            
                        // If title is empty after trimming, log an error or skip the button
                        if (empty($title)) {
                            Log::error('Empty button title encountered:', ['button' => $button]);
                            continue;
                            // Alternatively, set a default title:
                            // $title = 'Default Title';
                        }
            
                        // Limit title to 20 characters
                        $title = mb_substr($title, 0, 20);
    
                        $buttons[] = [
                            'type'    => 'postback',
                            'title'   => $title,
                            'payload' => $button['id'],
                        ];
                    }
        
                    // Ensure recipient is valid
                    if (empty($contact->phone)) {
                        Log::error('Invalid recipient phone:', ['contact' => $contact]);
                        $message->error = 'Invalid recipient phone';
                        $message->status = MessageStatusEnum::FAILED;
                        $message->save();
                        return false;
                    }
    
                    $postData = [
                        'recipient' => [
                            'id' => $contact->phone,
                        ],
                        'message' => [
                            'attachment' => [
                                'type'    => 'template',
                                'payload' => [
                                    'template_type' => 'button',
                                    'text'          => $message->value,
                                    'buttons'       => $buttons,
                                ],
                            ],
                        ],
                    ];
                    break;    
    
    
                default:
                    Log::error('Unsupported message type:', ['message_type' => $message_type]);
                    $message->error = 'Unsupported message type';
                    $message->status = MessageStatusEnum::FAILED;
                    $message->save();
                    return false;
        }

        // Ensure $postData is defined before using it
        if (empty($postData)) {
            Log::error('No $postData defined for message type:', ['message_type' => $message_type]);
            $message->error = 'No payload data defined';
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->post($url, $postData);

            $message_body = json_decode($response->body(), true);

            if (!empty($message_body['message_id'])) {
                $message->message_id = $message_body['message_id'];
                $message->status = MessageStatusEnum::SENT;
            } else {
                $message->error = isset($message_body['error']) ? $message_body['error']['message'] : 'Unknown';
                $message->status = MessageStatusEnum::FAILED;
            }

            $message->update();
            return true;
        } catch (\Throwable $th) {
            Log::error('sendMessengerMessage Error:', ['error' => $th->getMessage()]);
            $message->error = $th->getMessage();
            $message->status = MessageStatusEnum::FAILED;
            $message->save();
            return false;
        }
    }






    private function sendMessengerCampaignMessage($message)
    {
        $client = Client::active()->find($message->client_id);
        $contact = $message->contact;
        $template = $message->template_id;

        try {
            
            $client = Client::active()->find($message->client_id);
            $contact = $message->contact;
            $template = $message->campaign->template ?? $message->template;

            if (!empty($template)) {

                $response = [];
                $accessToken = getClientMessengerAccessToken($client);

                $url = $this->facebook_api."me/messages?access_token=".$accessToken;

                if ($message->message_type == 'template') {
                    $postData = [
                        'recipient' => [
                            'id' => $contact->phone
                        ],
                        'message' => [
                            'attachment' => $template->components[0]
                        ]
                    ];
                }
                
                try {

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->withoutVerifying()->post($url,$postData);
    
                    $message_body = json_decode($response->body(), true);

                    if (!empty($message_body['message_id'])) {
                        $message->message_id = $message_body['message_id'];
                        $message->status = MessageStatusEnum::SENT;
                    } else {
                        $message->error = isset($message_body['error']) ? $message_body['error']['message'] : 'Unknown';
                        $message->status = MessageStatusEnum::FAILED;
                    }
                    
                    $message->update();
                    return true;

                } catch (\Throwable $th) {
                    Log::info($th->getMessage());
                }

            } else {

                $message->error  = 'Template is empty';
                $message->status = MessageStatusEnum::FAILED;
                $message->update();
                return false;

            }
            
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
}
