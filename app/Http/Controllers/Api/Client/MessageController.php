<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\MessageEnum;
use App\Enums\TypeEnum;
use App\Traits\ImageTrait;
use App\Traits\TelegramTrait;
use App\Traits\WhatsAppTrait;
use Illuminate\Support\Facades\Storage;
use App\Enums\MessageStatusEnum;
use App\Helpers\TextHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatroomResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\NoteResource;
use App\Http\Resources\SharedFileResource;
use App\Http\Resources\SharedMediaResource;
use App\Http\Resources\StaffResource;
use App\Http\Resources\TagResource;
use App\Jobs\SendWhatsAppCampaignMessageJob;
use App\Models\Contact;
use App\Models\ContactNote;
use App\Models\ContactTag;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\User;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\MessageRepository;
use App\Repositories\Client\TeamRepository;
use App\Repositories\Client\TemplateRepository;
use App\Services\TemplateService;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\CommonTrait;
use App\Traits\RepoResponse;
use App\Traits\SendMailTrait;
use App\Traits\SendNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    use ApiReturnFormatTrait, CommonTrait, RepoResponse, SendMailTrait,SendNotification,TelegramTrait,WhatsAppTrait,ImageTrait;

    protected $repo;
    protected $contactRepository;
    protected $contact;
    protected $message;
    protected $template;
    protected $templateRepo;
    protected $messageModel;

    public function __construct(
        MessageRepository $repo,
        ContactRepository $contactRepository,
        Contact $contact,
        Message $messageModel,
        TemplateRepository $templateRepo,
        Template $template,
    ) {
        $this->repo         = $repo;
        $this->contactRepository = $contactRepository;
        $this->contact      = $contact;
        $this->messageModel = $messageModel;
        $this->templateRepo      = $templateRepo;
        $this->template     = $template;
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validator             = Validator::make($request->all(), [
            'message'     => 'required_without_all:image,document',
            'receiver_id' => 'required|exists:contacts,id',
            'image'       => 'required_without_all:message,document',
            'document'    => 'required_without_all:message,image',
        ]);
        
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        DB::beginTransaction();
        $user                  = jwtUser();
        $client                = $user->client;
        $activeSubscription    = $client->activeSubscription;
        $conversationRemaining = $activeSubscription->conversation_remaining;
        if ($activeSubscription->conversation_limit != -1 && $conversationRemaining <= 0) {
            return $this->responseWithError(__('insufficient_conversation_limit'), [], 403);
        }
        try {
            $contact         = $this->contact->where('client_id', $client->id)->find($request->receiver_id);
            if (is_null($contact)) {
                return $this->responseWithError(__('contact_not_found'), [], 404);
            }
            $conversation_id = $this->conversationUpdate($client->id, $request->receiver_id);
            if ($request->file('document') !== null) {
                $this->repo->sendDocumentMessage($request, $request->receiver_id, $contact->type, $conversation_id);
                $messageType = 'document';
            } elseif ($request->file('image') !== null) {
                $this->repo->sendImageMessage($request, $request->receiver_id, $contact->type, $conversation_id);
                $messageType = 'image';
            } elseif (! empty($request->message)) {
                $this->repo->sendTextMessage($request, $request->receiver_id, $contact->type, $conversation_id);
                $messageType = 'text';
            } else {
                return $this->responseWithError(__('no_valid_message_provided'), [], 400);
            }
            $conversationRemaining -= 1;
            Subscription::where('client_id', $client->id)
                ->where('status', 1)
                ->update(['conversation_remaining' => $conversationRemaining]);
            DB::commit();
            if (setting('is_pusher_notification_active')) {
                event(new \App\Events\ReceiveUpcomingMessage($client));
            }
            return $this->responseWithSuccess(__('message_sent_successfully'), [
                'message_type'            => $messageType,
                'conversation_id'         => $conversation_id,
                'remaining_conversations' => $conversationRemaining,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            logError('Error: ', $e);

            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function getTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        $user      = jwtUser();
        $client    = $user->client;
        $template  = $this->template->where('client_id', $client->id)->find($request->template_id);
        if (is_null($template)) {
            return $this->responseWithError(__('template_not_found'), [], 404);
        }
        $data      = app(TemplateService::class)->execute($template);

        return $this->responseWithSuccess(__('template_retrieved_successfully'), $data, 200);
    }

    public function sendTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id'   => 'required|exists:templates,id',
            'contact_id'    => 'required|exists:contacts,id',
            'body_values'   => 'nullable|array',
            'body_matchs'   => 'nullable|array',
            'button_values' => 'nullable|array',
            'button_matchs' => 'nullable|array',
            'image'         => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'document'      => 'nullable|file|mimes:pdf|max:5120',
            'video'         => 'nullable|file|mimes:mp4|max:10240',
            'audio'         => 'nullable|file|mimes:mp3|max:5120',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        DB::beginTransaction();
        try {
            $user                       = jwtUser();
            $client                     = $user->client;
            $activeSubscription         = $client->activeSubscription;
            if (! $activeSubscription) {
                return $this->responseWithError(__('no_active_subscription'), [], 200);
            }
            $campaignRemaining          = $activeSubscription->campaign_remaining;
            $conversationRemaining      = $activeSubscription->conversation_remaining;
            if ($activeSubscription->campaign_limit != -1 && $campaignRemaining <= 0) {
                return $this->responseWithError(__('insufficient_campaigns_limit'), [], 200);
            }
            if ($activeSubscription->conversation_limit != -1 && $conversationRemaining <= 0) {
                return $this->responseWithError(__('insufficient_conversation_limit'), [], 200);
            }
            // Code to handle media files
            $media_url                  = null;
            if ($request->hasFile('image')) {
                $response  = $this->saveImage($request->image);
                $media_url = getFileLink('original_image', $response['images']);
            } elseif ($request->hasFile('document')) {
                $media_url = asset('public/'.$this->saveFile($request->document, 'pdf', false));
            } elseif ($request->hasFile('video')) {
                $media_url = asset('public/'.$this->saveFile($request->video, 'mp4', false));
            } elseif ($request->hasFile('audio')) {
                $media_url = asset('public/'.$this->saveFile($request->audio, 'mp3', false));
            }

            $contact                    = $this->contact->where('client_id', $client->id)->find($request->contact_id);
            if (is_null($contact)) {
                return $this->responseWithError(__('contact_not_found'), [], 404);
            }
            $template                   = $this->template->where('client_id', $client->id)->find($request->template_id);
            if (is_null($template)) {
                return $this->responseWithError(__('template_not_found'), [], 404);
            }

            $body_values                = $request->body_values;
            $body_matchs                = $request->body_matchs;
            $button_values              = $request->button_values;
            $button_matchs              = $request->button_matchs;
            $content                    = null;
            $header_text                = null;
            $header_image               = null;
            $header_document            = null;
            $header_video               = null;
            $header_audio               = null;
            $footer_text                = null;
            $buttons                    = [];
            $template_components        = $template->components;
            $component_body             = [];
            $component_header           = [];
            $component_buttons          = [];
            foreach ($template_components as $component) {
                if ($component['type'] == 'HEADER') {
                    if ($component['format'] == 'TEXT') {
                        $header_text = $component['text'];
                    } elseif ($component['format'] == 'DOCUMENT') {
                        $component_header[] = [
                            'type'     => 'document',
                            'document' => [
                                'link' => $media_url,
                            ],
                        ];
                        $header_document    = $media_url;
                    } elseif ($component['format'] == 'IMAGE') {
                        $component_header[] = [
                            'type'  => 'image',
                            'image' => [
                                'link' => $media_url,
                            ],
                        ];
                        $header_image       = $media_url;
                    } elseif ($component['format'] == 'VIDEO') {
                        $component_header[] = [
                            'type'  => 'video',
                            'video' => [
                                'link' => $media_url,
                            ],
                        ];
                        $header_video       = $media_url;
                    } elseif ($component['format'] == 'AUDIO') {
                        $component_header[] = [
                            'type'  => 'audio',
                            'audio' => [
                                'link' => $media_url,
                            ],
                        ];
                        $header_audio       = $media_url;
                    }
                } elseif ($component['type'] == 'BODY') {
                    $content        = $component['text'];
                    $parameters     = [];
                    if (isset($body_values)) {
                        foreach ($body_matchs as $_key => $value) {
                            if ($value == 'input_value') {
                                $parameters[] = [
                                    'type' => 'text',
                                    'text' => $body_values[$_key],
                                ];
                                $content      = str_replace('{{'.$_key.'}}', $body_values[$_key], $content);
                            } elseif ($value == 'contact_name') {
                                $parameters[] = [
                                    'type' => 'text',
                                    'text' => $contact->name,
                                ];
                                $content      = str_replace('{{'.$_key.'}}', $contact->name, $content);
                            } elseif ($value == 'contact_phone') {
                                $parameters[] = [
                                    'type' => 'text',
                                    'text' => $contact->phone,
                                ];
                                $content      = str_replace('{{'.$_key.'}}', $contact->phone, $content);
                            } else {
                                $parameters[] = [
                                    'type' => 'text',
                                    'text' => '',
                                ];
                            }
                        }
                    }
                    $component_body = $parameters;
                } elseif ($component['type'] == 'BUTTONS') {
                    $buttons_for_chat  = [];
                    $buttons_compo     = [];
                    foreach ($component['buttons'] as $btn_key => $button) {
                        $i = 0;
                        if (isset($button_matchs) && (($button['type'] == 'URL' && stripos($button['url'], '{{') !== false) || ($button['type'] == 'COPY_CODE'))) {
                            foreach ($button_matchs as $btn_key => $value) {
                                $parameters               = [];
                                $button_content           = '';
                                $buttonData               = [
                                    'type'       => 'button',
                                    'sub_type'   => $button['type'],
                                    'index'      => $i.'',
                                    'parameters' => $parameters,
                                ];
                                $type                     = 'text';
                                if ($button['type'] == 'COPY_CODE') {
                                    $type           = 'coupon_code';
                                    $button_content = $button_values[$btn_key];
                                } else {
                                    $button_content = $button['url'];
                                }
                                if ($value == 'input_value') {

                                    $parameters[]   = [
                                        'type' => $type,
                                        $type  => $button_values[$btn_key],
                                    ];
                                    $button_content = str_replace('{{'.$btn_key.'}}', $button_values[$btn_key], $button_content);
                                } elseif ($value == 'contact_name') {
                                    $parameters[]   = [
                                        'type' => $type,
                                        $type  => $contact->name,
                                    ];
                                    $button_content = $button['url'];

                                    $button_content = str_replace('{{'.$btn_key.'}}', $contact->name, $button_content);
                                } elseif ($value == 'contact_phone') {
                                    $parameters[]   = [
                                        'type' => $type,
                                        $type  => $contact->phone,
                                    ];
                                    $button_content = $button['url'];
                                    $button_content = str_replace('{{'.$btn_key.'}}', $contact->phone, $button_content);
                                }
                                unset($button['example']);
                                $buttonData['parameters'] = $parameters;
                                $buttons_compo[]          = $buttonData;

                                $buttons_for_chat[]       = [
                                    'type' => $button['type'],
                                    'text' => $button['text'],
                                    'url'  => $button_content,
                                ];
                                $i++;
                            }
                        } else {
                            unset($button['example']);
                            $buttonData         = [
                                'type'       => 'button',
                                'sub_type'   => $button['type'],
                                'index'      => $i.'',
                                'parameters' => $button,
                            ];
                            $buttons_for_chat[] = $button;
                        }
                    }

                    $component_buttons = $buttons_compo;
                    $buttons           = $buttons_for_chat;
                } elseif ($component['type'] == 'FOOTER') {
                    $footer_text = $component['text'];
                }
            }
            $result                     = array_merge($component_header, $component_body, $component_buttons);
            $jsonResult                 = json_encode($result, JSON_UNESCAPED_UNICODE);
            $message                    = new $this->messageModel;
            $message->contact_id        = $contact->id;
            $message->template_id       = $template->id;
            $message->client_id         = $client->id;
            $message->header_text       = $header_text;
            $message->footer_text       = $footer_text;
            $message->header_image      = $header_image;
            $message->header_audio      = $header_audio;
            $message->header_video      = $header_video;
            $message->header_location   = $request->header_location;
            $message->header_document   = $header_document;
            $message->buttons           = json_encode($buttons);
            $message->value             = $content;
            $message->error             = null;
            $message->message_type      = MessageEnum::TEXT;
            $message->status            = MessageStatusEnum::SCHEDULED;
            $scheduleTime               = Carbon::now();
            $message->schedule_at       = $scheduleTime;
            $message->components        = $jsonResult;
            $message->component_header  = json_encode($component_header);
            $message->component_body    = json_encode($component_body);
            $message->component_buttons = json_encode($component_buttons);
            $message->campaign_id       = null;
            $message->is_campaign_msg   = 1;
            $message->save();
            $conversation_remaining     = $conversationRemaining - 1;
            Subscription::where('client_id', $client->id)->where('status', 1)->latest()->update(['conversation_remaining' => $conversation_remaining]);
            $this->conversationUpdate($client->id, $contact->id);
            SendWhatsAppCampaignMessageJob::dispatch($message);
            DB::commit();

            return $this->responseWithSuccess(__('created_successfully'), $message, 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->responseWithError($e->getMessage(), [], 200);
        }
    }


    // public function clearChat(Request $request)
    // {
    //     if (isDemoMode()) {
    //         return $this->responseWithError(__('this_function_is_disabled_in_demo_server'), [], 422);
    //     }
    //     $validator = Validator::make($request->all(), [
    //         'contact_id' => 'required|exists:contacts,id',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
    //     }
    //     try {
    //         $user = jwtUser();
    //         $client = $user->client;
    //         $messages = Message::where('contact_id', $request->contact_id)
    //             ->where('client_id', $client->id)
    //             ->get();

    //         foreach ($messages as $message) {
    //             if (!empty($message->header_image)) {
    //                 Storage::delete($message->header_image);
    //             }
    //             if (!empty($message->header_audio)) {
    //                 Storage::delete($message->header_audio);
    //             }
    //             if (!empty($message->header_video)) {
    //                 Storage::delete($message->header_video);
    //             }
    //             if (!empty($message->header_document)) {
    //                 Storage::delete($message->header_document);
    //             }
    //         }
    //         Message::where('contact_id', $request->contact_id)
    //             ->where('client_id', $client->id)
    //             ->delete();
    //         return $this->responseWithSuccess(__('deleted_successfully'), [], 200);
    //     } catch (\Throwable $e) {
    //         logError('Error clearing chat : ', $e);
    //         return $this->responseWithError($e->getMessage(), [], 200);
    //     }
    // }

    public function chatRooms(Request $request): JsonResponse
    {
        $rooms = $this->contactRepository->getChatContactList([
            'type'        => $request->type,
            'assignee_id' => $request->assignee_id,
            'q'           => $request->q,
            'tag_id'      => $request->tag_id,
            'is_seen'     => $request->is_seen,
        ]);
        
        try {
            $data = [
                'chat_rooms'    => ChatroomResource::collection($rooms),
                'total_unread_messages' => Message::where('status', MessageStatusEnum::DELIVERED->value)
                ->where('is_contact_msg', 1)
                ->count(),
                'next_page_url' => (bool) $rooms->nextPageUrl(),
            ];

            return $this->responseWithSuccess('chat_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }


    
    public function chatroomMessages($id): JsonResponse
    {
        try {
            $contact          = $this->contactRepository->find($id);
            if ($contact) {
                $contact->has_unread_conversation = 0;
                $contact->save();
            }
            $messages         = $this->repo->chatRoomMessages($id, null, $contact->source);
            $grouped_messages = $messages->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('d/m/Y');
            });
            Message::where('contact_id', $contact->id)->where('status', MessageStatusEnum::DELIVERED)->where('is_contact_msg', 1)->update([
                'status' => MessageStatusEnum::READ,
            ]);
            $data             = [
                'messages'      => $this->parseMessages($grouped_messages),
                'user'          => [
                    'id'                   => $contact->id,
                    'receiver_id'          => $contact->id,
                    'name'                 => $contact->name,
                    'phone'                => isDemoMode() ? '+*************' : @$contact->phone,
                    'image'                => $contact->profile_pic,
                    'group_chat_id'        => $contact->group_chat_id,
                    'source'               => $contact->type,
                    'last_conversation_at' => $contact->last_conversation_at,
                    'assignee_id'          => nullCheck($contact->assignee_id),
                    'conversation_id'      => nullCheck(@$contact->lastConversation->unique_id),
                    'user_type'            => "User",
                    'created_at'           => Carbon::parse($contact->created_at)->format('d/m/Y'),
                ],
                'next_page_url' => (bool) $messages->nextPageUrl(),
                'can_not_reply' => Carbon::now()->diffInHours($contact->last_conversation_at) > 24,
            ];

            return $this->responseWithSuccess('messages_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
    // public function deleteMessage(Request $request)
    // {
    //     if (isDemoMode()) {
    //         $data = [
    //             'status' => false,
    //             'message'  => __('this_function_is_disabled_in_demo_server'),
    //         ];
    //         return response()->json($data);
    //     }
    //     try {
    //         $user = jwtUser();
    //         $client = $user->client;
    //         $validator = Validator::make($request->all(), [
    //             'message_id' => 'required|exists:messages,id',
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
    //         }

    //         $message = Message::where('client_id', $client->id)->findOrFail($request->message_id);
    //         if (!empty($message->header_image)) {
    //             Storage::delete($message->header_image);
    //         }
    //         if (!empty($message->header_audio)) {
    //             Storage::delete($message->header_audio);
    //         }
    //         if (!empty($message->header_video)) {
    //             Storage::delete($message->header_video);
    //         }
    //         if (!empty($message->header_document)) {
    //             Storage::delete($message->header_document);
    //         }
    //         $message->delete();
    //         return $this->responseWithSuccess(__('deleted_successfully'), [], 200);
    //     } catch (\Throwable $e) {
    //         logError('Error Delete chat : ', $e);
    //         return $this->responseWithError($e->getMessage(), [], 200);
    //     }
    // }


    // public function sendForwardMessage(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'contact_id' => 'required|exists:contacts,id',
    //             'message_id' => 'required|exists:messages,id',
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
    //         }
    //         $user = jwtUser();
    //         $client = $user->client;
    //         $contact                    = $this->contact->where('client_id', $client->id)->findOrFail($request->contactId);
    //         $message                    = Message::find($request->messageIds);
    //         $forward                    = new Message();
    //         $forward->contact_id        = $contact->id;
    //         $forward->client_id         = $client->id;
    //         $forward->template_id       = $message->template_id;
    //         $forward->contacts          = $message->contacts;
    //         $forward->header_text       = $message->header_text;
    //         $forward->footer_text       = $message->footer_text;
    //         $forward->header_image      = $message->header_image;
    //         $forward->header_audio      = $message->header_audio;
    //         $forward->header_video      = $message->header_video;
    //         $forward->header_location   = $message->header_location;
    //         $forward->header_document   = $message->header_document;
    //         $forward->file_info         = $message->file_info;
    //         $forward->caption           = $message->caption;
    //         $forward->buttons           = $message->buttons;
    //         $forward->value             = $message->value;
    //         $forward->component_header  = $message->component_header;
    //         $forward->component_body    = $message->component_body;
    //         $forward->component_buttons = $message->component_buttons;
    //         $forward->message_type      = $message->message_type;
    //         $forward->status            = MessageStatusEnum::SENDING;
    //         $forward->source            = $message->source;
    //         $forward->components        = $message->components;
    //         $forward->is_contact_msg    = 0;
    //         $forward->is_campaign_msg   = 0;
    //         $forward->save();
    //         if ($forward->source->value == TypeEnum::TELEGRAM->value) {
    //             $this->sendTelegramMessage($forward, $forward->message_type);
    //         } else {
    //             $this->sendWhatsAppMessage($forward, $forward->message_type);
    //         }
    //         DB::commit();
    //         return $this->responseWithSuccess(__('message_sent_successfully'), [], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         logError('Error: ', $e);
    //         return $this->responseWithError($e->getMessage(), [], 200);
    //     }
    // }


    public function contactMessages(Request $request): JsonResponse
    {
        try {
            $user = jwtUser();
            $client = $user->client;
            $contact          = $this->contact->where('client_id', $client->id)->find($request->contact_id);
            $messages         = $this->repo->chatRoomMessages($request->contact_id, null, $contact->source);
            $grouped_messages = $messages->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('d/m/Y');
            });
            Message::where('contact_id', $contact->id)->where('status', MessageStatusEnum::DELIVERED)->where('is_contact_msg', 1)->update([
                'status' => MessageStatusEnum::READ,
            ]);
            $data             = [
                'messages'      => $this->parseMessages($grouped_messages),
                'user'          => [
                    'id'                   => $contact->id,
                    'receiver_id'          => $contact->id,
                    'name'                 => $contact->name,
                    'phone'                => isDemoMode() ? '+*************' : @$contact->phone,
                    'image'                => $contact->profile_pic,
                    'group_chat_id'        => $contact->group_chat_id,
                    'source'               => $contact->type,
                    'last_conversation_at' => $contact->last_conversation_at,
                    'assignee_id'          => nullCheck($contact->assignee_id),
                ],
                'next_page_url' => (bool) $messages->nextPageUrl(),
                'can_not_reply' => Carbon::now()->diffInHours($contact->last_conversation_at) > 24,
                'paginate' => [
                    'total'             => $messages->total(),
                    'current_page'      => $messages->currentPage(),
                    'per_page'          => $messages->perPage(),
                    'last_page'         => $messages->lastPage(),
                    'prev_page_url'     => $messages->previousPageUrl(),
                    'next_page_url'     => $messages->nextPageUrl(),
                    'path'              => $messages->path(),
                ],
            ];

            return $this->responseWithSuccess('messages_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    
    public function parseMessages($messages): array
    {
        $data = [];
        $i    = 0;
        foreach ($messages as $key => $message) {
            if ($key == Carbon::now()->format('d/m/Y')) {
                $key = 'Today';
            } elseif ($key == Carbon::now()->subDay()->format('d/m/Y')) {
                $key = 'Yesterday';
            }
            $data[$i]['date'] = $key;
            foreach ($message->reverse() as $message_item) {

                $receiver_image         = null;
                $contact_name           = null;
                $contacts               = $message_item->contacts ? json_decode($message_item->contacts) : [];
                if ($message_item->contact->type == 'telegram') {
                    $receiver_image = isset($message_item->group_subscriber) && isset($message_item->group_subscriber->avatar) ? $message_item->group_subscriber->avatar : static_asset('images/default/user.jpg');
                    $contact_name   = isset($message_item->group_subscriber) && isset($message_item->group_subscriber->name) ? $message_item->group_subscriber->name : null;
                } else {
                    $receiver_image = isset($message_item->contact->profile_pic) && ! empty($message_item->contact->profile_pic) ? $message_item->contact->profile_pic : static_asset('images/default/user.jpg');
                    $contact_name   = isset($message_item->contact->name) ? $message_item->contact->name : null;
                }
                // dd($message_item->group_subscriber);
                $buttons                = $this->renderButtons($message_item->buttons);
                $class                  = 'single-sp-chat-area mt--8 ';
                if ($message_item->is_campaign_msg) {
                    $class .= 'single-sp-card-box';
                } elseif ($message_item->header_image) {
                    $class .= 'single-sp-img-box';
                } elseif ($message_item->header_video) {
                    $class .= 'single-sp-card-box plyr';
                } elseif ($message_item->header_audio) {
                    $class .= 'single-sp-audio-box plyr';
                } elseif ($message_item->header_document) {
                    $class .= 'single-sp-card-box';
                } elseif ($message_item->contacts) {
                    $class .= 'single-sp-card-box d-flex mt--23';
                } else {
                    $class .= 'single-sp-text-area';
                }
                $context                = [];
                if ($message_item->context_id) {
                    $contextMessage = $this->messageModel->where('message_id', $message_item->context_id)->first();
                    if ($contextMessage) {
                        $contextType  = $contextMessage->message_type;
                        $contextValue = match ($contextType) {
                            'text'      => Str::limit($contextMessage->value, 100),
                            'image'     => $contextMessage->header_image,
                            'location'  => $contextMessage->header_location,
                            'audio'     => $contextMessage->header_audio,
                            'video'     => $contextMessage->header_video,
                            'document'  => $contextMessage->header_document,
                            'contacts'  => $contextMessage->contacts,
                            'reply_button', 'interactive_button' => $contextMessage->header_text ? $contextMessage->header_text : $contextMessage->value,
                            'interactive', 'button' => $contextMessage->buttons ? json_decode($contextMessage->buttons, true)[0]['text'] : $contextMessage->value,
                            'condition' => $contextMessage->component_header,
                            'interactive_list', 'template', 'carousel' => $contextMessage->components,
                            default     => null,
                        };
                        $context      = [
                            'id'      => $contextMessage->id,
                            'type'    => $contextType,
                            'message' => $contextValue,
                        ];
                    }
                }
                $class .= $message_item->is_contact_msg ? '' : ' text-end';
                // $class .= $message_item->is_contact_msg ? '' : (($message_item->header_video || $message_item->header_audio) && !$message_item->is_campaign_msg ? '' : ' text-end');
                // Correctly fetch and set the message type
                $messageType            = $message_item->message_type;
                $data[$i]['messages'][] = [
                    'id'              => $message_item->id,
                    'class'           => $class,
                    'context'         => $context,
                    'is_campaign_msg' => (bool) $message_item->is_campaign_msg,
                    'header_video'    => $message_item->header_video,
                    'header_image'    => $message_item->header_image,
                    'header_audio'    => $message_item->header_audio,
                    'message_type'    => $messageType,
                    'header_document' => $message_item->header_document,
                    'header_location' => $message_item->header_location,
                    'file_info'       => $message_item->file_info ?: [],
                    'header_text'     => $message_item->header_text,
                    'value'           => TextHelper::transformText($message_item->value),
                    'footer_text'     => $message_item->footer_text,
                    'contacts'        => $contacts,
                    'error'           => $message_item->error,
                    'is_seen'         => $message_item->status == MessageStatusEnum::READ,
                    'is_sent'         => $message_item->status == MessageStatusEnum::SENT,
                    'is_delivered'    => $message_item->status == MessageStatusEnum::DELIVERED,
                    'user_image'      => @$message_item->client->profile_pic,
                    'receiver_image'  => $receiver_image,
                    'contact_name'    => $contact_name,
                    'source'          => $message_item->contact->type,
                    'sent_at'         => Carbon::parse($message_item->created_at)->format('H:i A'),
                    'is_contact_msg'  => (bool) $message_item->is_contact_msg,
                    'buttons'         => $buttons,
                ];
            }
            $i++;
        }

        return $data;
    }

    private function renderButtons($buttonsJson): array
    {
        $renderedButtons = [];
        $buttons         = json_decode($buttonsJson, true);
        if (! is_array($buttons)) {
            return $renderedButtons;
        }
        foreach ($buttons as $button) {
            if (! empty($button['parameters'])) {
                $params            = $button['parameters'][0];
                $type              = $params['type'] ?? null;

                $renderedButtons[] = [
                    'type'  => $type == 'URL' ? 'a' : 'button',
                    'text'  => getArrayValue($type, $params),
                    'value' => getArrayValue($type, $button),
                ];
            } else {
                $type              = $button['type'] ?? 'URL';

                $renderedButtons[] = [
                    'type'  => $type == 'URL' ? 'a' : 'button',
                    'text'  => getArrayValue('text', $button),
                    'value' => getArrayValue('url', $button),
                ];
            }
        }

        return $renderedButtons;
    }

    public function sendForwardMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'messageIds' => 'required',
            'contactId'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return $this->repo->sendForwardMessage($request);
    }

    public function contactByClient(Request $request): JsonResponse
    {
        $contacts = $this->contactRepository->activeContacts([
            'client_id'   => jwtUser()->client->id,
            'type'        => $request->type,
            'assignee_id' => $request->assignee_id,
            'q'           => $request->q,
            'tag_id'      => $request->tag_id,
            'is_seen'     => $request->is_seen,
        ]);

        try {
            $data = [
                'contacts'      => ContactResource::collection($contacts),
                'next_page_url' => (bool) $contacts->nextPageUrl(),
            ];

            return $this->responseWithSuccess('contact_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }


    public function staffsByClient(): JsonResponse
    {
        $user   = jwtUser();
        $client = $user->client;
        $repo   = new TeamRepository;
        $staffs = $repo->clientStaffs($client->id, auth()->id());
        
        try {
            $data = [
                'staffs' => StaffResource::collection($staffs),
            ];

            return $this->responseWithSuccess('staffs_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function assignStaff(Request $request): JsonResponse
    {
        try {

            $contact              = $this->contactRepository->find($request->contact_id);
            $contact->assignee_id = $request->staff_id;
            $contact->save();
            $user                 = User::find($request->staff_id);

            $msg                  = __('conatct_assign_for_chat', ['assignedByName' => Auth::user()->first_name.' '.Auth::user()->last_name]);

            $this->pushNotification([
                'ids'     => $user->onesignal_player_id,
                'message' => $msg,
                'heading' => __('chat_contact_assignment'),
                'url'     => route('client.chat.index'),
            ]);

            $data                 = [
                'user'            => $user,
                'chat_link'       => route('client.chat.index', ['contact' => $contact->id]),
                'subject'         => __('chat_contact_assignment'),
                'body'            => $msg,
                'email_templates' => $msg,

            ];

            if (isMailSetupValid()) {
                // Mail::to($user->email)->send(new SendSmtpMail($attribute));
                $this->sendmail($user->email, 'emails.conatct_assign', $data);
            }

            return response()->json([
                'status'  => true,
                'success' => __('staff_assigned_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => __('staff_unassigned_successfully'),
                'error'   => $e->getMessage(),
              ], 200);
            // return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function contactDetails($id): JsonResponse
    {
        try {
            $contact = $this->contactRepository->find($id);
            $notes   = ContactNote::where('contact_id', $id)->latest()->get();
            $tags    = ContactTag::where('contact_id', $id)->orderBy('id', 'DESC')->get();
            $data    = [
                'contact' => [
                    'id'                   => $contact->id,
                    'receiver_id'          => $contact->id,
                    'name'                 => $contact->name,
                    'phone'                => (isDemoMode()) ? '*********' : $contact->phone,
                    'image'                => $contact->profile_pic,
                    'last_conversation_at' => Carbon::parse($contact->last_conversation_at)->format('d/m/Y'),
                    'assignee_id'          => nullCheck($contact->assignee_id),
                    'conversation_id'      => nullCheck(@$contact->lastConversation->unique_id),
                    'source'               => $contact->type,
                    'created_at'           => Carbon::parse($contact->created_at)->format('d/m/Y'),
                ],
                'notes'   => NoteResource::collection($notes),
                'tags'    => TagResource::collection($tags),
            ];

            return $this->responseWithSuccess('chat_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function sharedFiles($id, Request $request): JsonResponse
    {
        try {
            $medias = $this->repo->chatRoomMessages($id, $request->type);
            if ($request->type == 'media') {
                $files = SharedMediaResource::collection($medias);
            } elseif ($request->type == 'files') {
                $files = SharedFileResource::collection($medias);
            } else {
                $files = [];
                foreach ($medias as $media) {
                    $pattern       = '/https?:\/\/\S+/';

                    preg_match_all($pattern, $media->value, $matches);

                    $extractedUrls = $matches[0];

                    foreach ($extractedUrls as $url) {
                        $files[] = [
                            'path' => $url,
                            'type' => 'link',
                        ];
                    }
                }
            }
            $data   = [
                'files'         => $files,
                'success'       => true,
                'next_page_url' => $medias->nextPageUrl() ?: false,
            ];

            return $this->responseWithSuccess('shared_files_retrived_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function deleteFile($id): JsonResponse
    {
        try {
            $message = $this->repo->find($id);
            if ($message->header_image) {
                File::delete($message->header_image);
                $message->header_image = null;
            } elseif ($message->header_document) {
                File::delete($message->header_document);
                $message->header_document = null;
            }
            $message->save();

            return $this->responseWithSuccess('file_deleted_successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function clearChat($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }

        return $this->repo->clearChat($id);
    }

    public function deleteMessage($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }

        return $this->repo->deleteMessage($id);
    }

    public function generateAIReply(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'reply_type' => 'required|string',
            'context'    => 'required|array',
        ]);
        $reply = $this->repo->generateAIReply(
            $request->contact_id,
            $request->reply_type,
            $request->context
        );

        return response()->json($reply);
    }

    public function generateAIRewriteReply(Request $request)
    {
        $reply = $this->repo->generateAIRewriteReply(
            $request->contact_id,
            $request->reply_type,
            $request->context
        );

        return response()->json($reply);
    }

    public function getContactMessages($contactId, Request $request)
    {
        $messages = Message::where('contact_id', $contactId)
            ->orderBy('created_at', 'desc')
            ->where('is_contact_msg', 1)
            ->limit($request->limit ?? 1)
            // ->latest()
            // ->limit(3)
            ->pluck('value');

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function index(Request $request)
    {
        try {
            $contact = $this->contact->find($request->contact);

            $data    = [
                'contact' => $contact ? [
                    'id'                   => $contact->id,
                    'receiver_id'          => $contact->id,
                    'name'                 => $contact->name,
                    'phone'                => $contact->phone,
                    'image'                => $contact->profile_pic,
                    'last_conversation_at' => $contact->last_conversation_at,
                    'assignee_id'          => nullCheck($contact->assignee_id),
                ] : false,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }
}
