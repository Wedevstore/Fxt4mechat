<?php

namespace App\Repositories\Client\Web;

use App\Traits\MessengerTrait;
use App\Enums\MessageEnum;
use App\Enums\MessageStatusEnum;
use App\Enums\TypeEnum;
use App\Models\ChatRoom;
use App\Models\Contact;
use App\Models\Message;
use App\Services\OpenAIService;
use App\Traits\ImageTrait;
use App\Traits\RepoResponse;
use App\Traits\TelegramTrait;
use App\Traits\WhatsAppWebTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class MessageRepository
{
    use ImageTrait, RepoResponse, TelegramTrait;

    use MessengerTrait {
        MessengerTrait::handleReceivedMedia as handleMessengerMedia;
    }
    use WhatsAppWebTrait {
        WhatsAppWebTrait::handleReceivedMedia as handleWhatsAppMedia;
    }

    private $model;

    private $contact;

    public function __construct(
        Message $model,
        Contact $contact,
    ) {
        $this->model   = $model;
        $this->contact = $contact;
    }

    public function all()
    {
        return Contact::latest()->withPermission()->paginate(setting('pagination'));
    }

    public function getChatContactList()
    {
        return $this->model->withPermission()->where('has_conversation', 1)->with(['conversation', 'country'])->orderBy('Me', 'DESC')->limit(50)->get();
    }

    public function handleReceivedMedia()
    {
        // You can call one or both methods explicitly
        if (trait_exists('App\Traits\MessengerTrait')) {
            $this->handleMessengerMedia();
        }
        $this->handleWhatsAppMedia();
    }

    public function sendTextMessage($request, $contact_id, $source, $conversation_id = null, $session)
    {
        $message_type            = MessageEnum::TEXT->value;
        $message                 = new $this->model;
        $message->contact_id     = $contact_id;
        if ($conversation_id) {
            $message->conversation_id = $conversation_id;
        }
        if ($request->reply_message_id) {
            $context = $this->model->find($request->reply_message_id);
            if ($context) {
                $message->context_id = $context->message_id;
            } else {
                Log::error('Invalid reply message ID.', ['reply_message_id' => $request->reply_message_id]);
                // throw new \Exception('Invalid reply message ID.');
            }
        }

        $message->client_id      = Auth::user()->client_id;
        $message->value          = strip_tags($request->message);
        $message->message_type   = MessageEnum::TEXT;
        $message->is_contact_msg = 0;
        $message->source         = $source;
        $message->status         = MessageStatusEnum::SENT;
        $message->save();
        if ($source == 'whatsapp') {
            $this->sendWhatsAppWebMessage($message, $message_type, null, $session);
        } elseif ($source == 'telegram') {
            $this->sendTelegramMessage($message, $message_type);
        } elseif(($source == 'messenger' || $source == 'instagram') && trait_exists('App\Traits\MessengerTrait')) {
            $this->sendMessengerMessage($message, $message_type);
        }

        return $message;
    }

    public function sendDocumentMessage($request, $contact_id, $source, $conversation_id = null, $session)
    {
        try {
            $message_type            = MessageEnum::DOCUMENT->value;
            $contact                 = $this->contact->findOrFail($contact_id);
            $message                 = new $this->model;
            $message->contact_id     = $contact->id;
            if ($conversation_id) {
                $message->conversation_id = $conversation_id;
            }
            if ($request->reply_message_id) {
                $context = $this->model->find($request->reply_message_id);
                if ($context) {
                    $message->context_id = $context->message_id;
                } else {
                    Log::error('Invalid reply message ID.', ['reply_message_id' => $request->reply_message_id]);
                    // throw new \Exception('Invalid reply message ID.');
                }
            }
            $message->client_id      = Auth::user()->client_id;
            $file_info               = [];
            if ($request->hasFile('document')) {
                $file                     = $request->file('document');
                $fileExtension            = $file->getClientOriginalExtension();
                $file_info                = [
                    'name' => $request->file('document')->getClientOriginalName(),
                    'size' => round($request->file('document')->getSize() / 1024, 2),
                    'ext'  => $fileExtension,
                ];
                $media_url                = asset('public/'.$this->saveFile($request->document, $fileExtension, false));
                if (isDemoMode()) {
                    $media_url = 'https://spagreen.sgp1.cdn.digitaloceanspaces.com/SaleBot/DemoContent/salebot_chatwidget.pdf';
                    $caption = '[DEMO Caption]';
                }
                $message->header_document = $media_url;
            }
            $message->message_type   = MessageEnum::DOCUMENT;
            $message->file_info      = $file_info;
            $message->is_contact_msg = 0;
            $message->status         = MessageStatusEnum::SENT;
            $message->save();
            if ($source == 'whatsapp') {
                $this->sendWhatsAppWebMessage($message, $message_type, null, $session);
            } elseif ($source == 'telegram') {
                $this->sendTelegramMessage($message, $message_type);
            }  elseif (($source == 'messenger' || $source == 'instagram') && trait_exists('App\Traits\MessengerTrait')) {
                $this->sendMessengerMessage($message, $message_type);
            }

            return $this->ajaxResponse(200, __('created_successfully'), '');
            
        } catch (\Throwable $e) {
            logError('send Document Message : ', $e);

            return $this->ajaxResponse(200, __('an_unexpected_error_occurred_please_try_again_later.'), '');
        }
    }

    public function sendImageMessage($request, $contact_id, $source, $conversation_id = null, $session)
    {
        try {
            $contact = $this->contact->findOrFail($contact_id);
            $message = new $this->model;
            if ($conversation_id) {
                $message->conversation_id = $conversation_id;
            }
            if ($request->reply_message_id) {
                $context = $this->model->find($request->reply_message_id);
                if ($context) {
                    $message->context_id = $context->message_id;
                } else {
                    // Log::error('Invalid reply message ID.', ['reply_message_id' => $request->reply_message_id]);
                    // throw new \Exception('Invalid reply message ID.');
                }
            }
            $file    = $request->file('image');
            if (! empty($file)) {
                $mimeType              = $file->getClientMimeType();
                $fileExtension         = $file->getClientOriginalExtension();
                switch ($mimeType) {
                    case strpos($mimeType, 'image') !== false:
                        $response              = $this->saveImage($file);
                        $mediaUrl              = getFileLink('original_image', $response['images']);
                        $messageType           = MessageEnum::IMAGE;
                        if (isDemoMode()) {
                            $mediaUrl = 'https://spagreen.sgp1.cdn.digitaloceanspaces.com/SaleBot/DemoContent/Inline.png';
                        }
                        $message->header_image = $mediaUrl;
                        break;
                    case strpos($mimeType, 'audio') !== false:
                        $mediaUrl              = asset('public/'.$this->saveFile($file, $fileExtension, false));
                        $messageType           = MessageEnum::AUDIO;
                        if (isDemoMode()) {
                    		$mediaUrl = 'https://spagreen.sgp1.cdn.digitaloceanspaces.com/SaleBot/DemoContent/audio.mp3';
                		}
                        $message->header_audio = $mediaUrl;
                        break;
                    case strpos($mimeType, 'video') !== false:
                        $mediaUrl              = asset('public/'.$this->saveFile($file, $fileExtension, false));
                        $messageType           = MessageEnum::VIDEO;
                        if (isDemoMode()) {
                            $caption = '[DEMO Caption]';
                            $mediaUrl = 'https://spagreen.sgp1.cdn.digitaloceanspaces.com/SaleBot/DemoContent/video.mp4';
                        }
                        $message->header_video = $mediaUrl;
                        break;
                    default:
                        exit();
                        break;
                }
                $message->contact_id   = $contact->id;
                $message->client_id    = Auth::user()->client_id;
                $message->message_type = $messageType ?? null;
                $message->status       = MessageStatusEnum::SENDING;
                $message->save();
                if ($source == 'whatsapp') {
                    $response = $this->sendWhatsAppWebMessage($message, $messageType->value, null, $session);
                } elseif ($source == 'telegram') {
                    $response = $this->sendTelegramMessage($message, $messageType->value);
                } elseif (($source == 'messenger' || $source == 'instagram') && trait_exists('App\Traits\MessengerTrait')) {
                    $response = $this->sendMessengerMessage($message, $messageType->value);
                }

                return $this->ajaxResponse(200, __('created_successfully'), '');
            } else {
                return $this->ajaxResponse(400, __('No file uploaded'), '');
            }
        } catch (\Throwable $e) {
            logError('send Image Message : ', $e);

            return $this->ajaxResponse(500, __('an_unexpected_error_occurred_please_try_again_later.'), '');
        }
    }

    public function firstChatroom($user)
    {
        return ChatRoom::with('user', 'receiver', 'lastMessage')->whereHas('lastMessage')->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('receiver_id', $user->id);
        })->latest()->first();
    }

    public function chatRoomExists($data)
    {
        return ChatRoom::where(function ($q) use ($data) {
            $q->where(function ($query) use ($data) {
                $query->where('user_id', $data['user_id'])->where('receiver_id', $data['receiver_id']);
            })->orWhere(function ($query) use ($data) {
                $query->where('user_id', $data['receiver_id'])->where('receiver_id', $data['user_id']);
            });
        })->latest()->first();
    }

    public function findChatroom($id): Model|Collection|Builder|array|null
    {
        return ChatRoom::with('user', 'receiver', 'lastMessage')->find($id);
    }

    // public function chatRoomMessages($id, $type = null, $source = null): LengthAwarePaginator
    // {
    //     return Message::where('contact_id', $id)->when($type == 'media', function ($query) {
    //         $query->where('header_image', '!=', '')->orWhere('header_video', '!=', '')->orWhere('header_audio', '!=', '');
    //     })->when($type == 'files', function ($query) {
    //         $query->where('header_document', '!=', '');
    //     })->when($type == 'links', function ($query) {
    //         $query->where('value', 'REGEXP', 'https?://[^ ]+');
    //     })
    //         ->withPermission()
    //     // ->latest()
    //         ->orderBy('id', 'DESC')
    //         ->paginate(1000);
    // }

    public function chatRoomMessages($id, $type = null, $source = null): LengthAwarePaginator
    {
        $query = Message::where('contact_id', $id)
            ->when($type == 'media', function ($query) {
                $query->where(function ($q) {
                    $q->where('header_image', '!=', '')
                      ->orWhere('header_video', '!=', '')
                      ->orWhere('header_audio', '!=', '');
                });
            })
            ->when($type == 'files', function ($query) {
                $query->where('header_document', '!=', '');
            })
            ->when($type == 'links', function ($query) {
                $query->whereRaw("value REGEXP 'https?://[^ ]+'");
            })
            ->withPermission()
            ->orderBy('id', 'DESC');

        return $query->paginate(1000);
    }

    public function messageUser($user, $data = []): LengthAwarePaginator
    {
        return ChatRoom::with('user', 'receiver', 'lastMessage')->whereHas('lastMessage')->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('receiver_id', $user->id);
        })->when(arrayCheck('q', $data), function ($query) use ($data) {
            $query->whereHas('receiver', function ($q) use ($data) {
                $q->where(function ($q) use ($data) {
                    $q->where('name', 'like', '%'.$data['q'].'%')->orWhere('phone', 'like', '%'.$data['q'].'%');
                });
            });
        })->latest()->paginate(10);
    }

    public function createChatRoom($data)
    {
        return ChatRoom::create([
            'user_id'     => $data['user_id'],
            'receiver_id' => $data['receiver_id'],
            'is_accepted' => 1,
        ]);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function clearChat($contact_id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ];

            return response()->json($data);
        }

        try {
            $messages = $this->model->where('contact_id', $contact_id)->withPermission()->get();

            foreach ($messages as $message) {
                if (! empty($message->header_image)) {
                    Storage::delete($message->header_image);
                }
                if (! empty($message->header_audio)) {
                    Storage::delete($message->header_audio);
                }
                if (! empty($message->header_video)) {
                    Storage::delete($message->header_video);
                }
                if (! empty($message->header_document)) {
                    Storage::delete($message->header_document);
                }
            }
            $this->model->where('contact_id', $contact_id)->delete();

            return $this->ajaxResponse(200, __('deleted_successfully'), '');
        } catch (\Throwable $e) {
            logError('Error clearing chat : ', $e);

            return $this->ajaxResponse(500, __('an_unexpected_error_occurred_please_try_again_later.'), '');
        }
    }

    public function deleteMessage($message_id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ];

            return response()->json($data);
        }
        try {
            $message = $this->model->withPermission()->findOrFail($message_id);
            if (! empty($message->header_image)) {
                Storage::delete($message->header_image);
            }
            if (! empty($message->header_audio)) {
                Storage::delete($message->header_audio);
            }
            if (! empty($message->header_video)) {
                Storage::delete($message->header_video);
            }
            if (! empty($message->header_document)) {
                Storage::delete($message->header_document);
            }
            $message->delete();

            return $this->ajaxResponse(200, __('deleted_successfully'), '');
        } catch (\Throwable $e) {
            logError('Error Delete chat : ', $e);

            return $this->ajaxResponse(500, __('an_unexpected_error_occurred_please_try_again_later.'), '');
        }
    }

    // MessageRepository.php

    public function generateAIReply($contact_id, $reply_type, $context)
    {
        // dd($context);
        $data        = [];
        $context     = $context[0]           ?? 'Hello';
        if (isDemoMode()) {
            return [
                'content' => '[DEMO MODE] This is a demonstration message. In a live environment, AiWriter will provide authentic content based on your inputs.',
                'success' => true,
            ];
        }
        $model       = 'gpt-3.5-turbo-instruct';
        $contact     = $this->contact->findOrFail($contact_id);
        $contactName = $contact->name; // Adjust according to your contact model

        $prompts     = [
            'professional' => "write a professional and courteous WhatsApp message replay & answer of the following context: {$context}",
            'emotional'    => "write a warm and emotional WhatsApp message replay & answer of the following context: {$context}",
            'funny'        => "write a humorous and entertaining WhatsApp message replay & answer of the following context: {$context}",
            'potential'    => "write a WhatsApp message that shows potential replay & answer of the following context: {$context}",
        ];
        $prompt      = $prompts[$reply_type] ?? "Generate a thoughtful and engaging message for the following context: {$context}";
        try {
            $open_ai_key         = Auth::user()->client->open_ai_key;
            $data['open_ai_key'] = $open_ai_key;
            $data['prompt']      = $prompt;
            $data['model']       = $model;
            $result              = app(OpenAIService::class)->execute($data);

            return $result;
        } catch (\Exception $e) {
            logError('Error: ', $e);

            return ['error' => $e->getMessage()];
        }
    }

    public function generateAIRewriteReply($contact_id, $reply_type, $context = null)
    {
        $data        = [];
        $context     = "$context[0] ?? ''";
        if (isDemoMode()) {
            return [
                'content' => '[DEMO MODE] This is a demonstration response. In a live environment, AiWriter will provide authentic content based on your inputs.',
                'success' => true,
            ];
        }
        $model       = 'gpt-3.5-turbo-instruct';
        $contact     = $this->contact->findOrFail($contact_id);
        $contactName = $contact->name; // Adjust according to your contact model
        $prompts     = [
            'professional' => "Please rewrite a professional and courteous response for the following context: {$context}",
            'emotional'    => "Please rewrite a warm and emotional response for the following context: {$context}",
            'funny'        => "Please rewrite a humorous and entertaining response for the following context: {$context}",
            'potential'    => "Please rewrite a response that shows potential and enthusiasm for the following context: {$context}",
        ];
        $prompt      = $prompts[$reply_type] ?? "Generate a thoughtful and engaging response for the following context: {$context}";
        try {
            $open_ai_key         = Auth::user()->client->open_ai_key;
            $data['open_ai_key'] = $open_ai_key;
            $data['prompt']      = $prompt;
            $data['model']       = $model;
            $data['max_tokens']  = 150; // Limit the length of the response
            $result              = app(OpenAIService::class)->execute($data);

            return $result;
        } catch (\Exception $e) {
            logError('Error: ', $e);

            return ['error' => $e->getMessage()];
        }
    }

    public function sendForwardMessage($request)
    {
        DB::beginTransaction();
        try {
            $contact                    = $this->contact->findOrFail($request->contactId);
            $message                    = $this->find($request->messageIds);
            $forward                    = new $this->model;
            $forward->contact_id        = $contact->id;
            $forward->client_id         = Auth::user()->client_id;
            $forward->template_id       = $message->template_id;
            $forward->contacts          = $message->contacts;
            $forward->header_text       = $message->header_text;
            $forward->footer_text       = $message->footer_text;
            $forward->header_image      = $message->header_image;
            $forward->header_audio      = $message->header_audio;
            $forward->header_video      = $message->header_video;
            $forward->header_location   = $message->header_location;
            $forward->header_document   = $message->header_document;
            $forward->file_info         = $message->file_info;
            $forward->caption           = $message->caption;
            $forward->buttons           = $message->buttons;
            $forward->value             = $message->value;
            $forward->component_header  = $message->component_header;
            $forward->component_body    = $message->component_body;
            $forward->component_buttons = $message->component_buttons;
            $forward->message_type      = $message->message_type;
            $forward->status            = MessageStatusEnum::SENDING;
            $forward->source            = $message->source;
            $forward->components        = $message->components;
            $forward->is_contact_msg    = 0;
            $forward->is_campaign_msg   = 0;
            $forward->save();
            if ($forward->source->value == TypeEnum::TELEGRAM->value) {
                $this->sendTelegramMessage($forward, $forward->message_type);
            } else {
                $this->sendWhatsAppMessage($forward, $forward->message_type, $session = null);
            }
            DB::commit();

            return $this->formatResponse(true, __('message_sent_successfully'), '', []);
        } catch (\Exception $e) {
            DB::rollBack();
            logError('Error: ', $e);

            return $this->formatResponse(false, $e->getMessage(), '', []);
        }
    }
}
