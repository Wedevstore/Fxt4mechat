<?php

namespace App\Repositories\Client\Web;
use Carbon\Carbon;
use App\Enums\TypeEnum;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Message;
use App\Models\Segment;
use App\Models\Campaign;
use App\Models\Template;
use App\Enums\StatusEnum;
use App\Models\ContactAttribute;
use App\Enums\MessageEnum;
use App\Traits\ImageTrait;
use App\Traits\WebCommonTrait;
use App\Models\ContactsList;
use App\Models\Subscription;
use App\Traits\RepoResponse;
use App\Traits\BotReplyTrait;
use App\Traits\TelegramTrait;
use App\Traits\WhatsAppTrait;
use App\Enums\MessageStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWhatsAppWebCampaignMessageJob;
use App\Jobs\SendWhatsAppWebCampaignSingleContactMessageJob;
use App\Models\Device;
use App\Models\WebTemplate;
use Illuminate\Support\Facades\Auth;

class WaCampaignRepository
{
    use WebCommonTrait, ImageTrait, RepoResponse, TelegramTrait, WhatsAppTrait,BotReplyTrait;

    private $model;

    private $contact;

    private $template;

    private $segment;

    private $contact_list;

    private $country;

    private $message;

    private $attribute;

    public function __construct(
        Campaign $model,
        Contact $contact,
        WebTemplate $template,
        Segment $segment,
        ContactsList $contact_list,
        Country $country,
        Message $message,
        ContactAttribute $attribute,
    ) {
        $this->model        = $model;
        $this->contact      = $contact;
        $this->template     = $template;
        $this->segment      = $segment;
        $this->contact_list = $contact_list;
        $this->country      = $country;
        $this->message      = $message;
        $this->attribute    = $attribute;
    }

    public function all()
    {
        return Campaign::latest()->withPermission()->paginate(setting('pagination'));
    }

    public function allDevice()
    {
        return Device::where('status','connected')->where('client_id', auth()->user()->client_id)->get();
    }

    public function activeSegments()
    {
        return Campaign::where('status', 1)->withPermission()->get();
    } 

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $client = auth()->user()->client;

            // Validate template
            $template = WebTemplate::find($request->template_id);
            if (!$template) {
                return $this->formatResponse(false, __('invalid_template_selected'), 'client.web.whatsapp.campaigns.index', []);
            }

            // Check active subscription
            $activeSubscription = $client->activeSubscription;
            if (!$activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.web.whatsapp.campaigns.index', []);
            }

            // Handle media upload (optional)
            $media_url = null;
            if ($request->hasFile('image')) {
                $response  = $this->saveImage($request->image);
                $media_url = getFileLink('original_image', $response['images']);
            } elseif ($request->hasFile('document')) {
                $media_url = asset('public/' . $this->saveFile($request->document, 'pdf', false));
            } elseif ($request->hasFile('video')) {
                $media_url = asset('public/' . $this->saveFile($request->video, 'mp4', false));
            } elseif ($request->hasFile('audio')) {
                $media_url = asset('public/' . $this->saveFile($request->audio, 'mp3', false));
            }


            $campaign                  = new $this->model;
            $campaign->campaign_name   = $request->campaign_name;
            $campaign->client_id       = Auth::user()->client_id;
            $campaign->web_template_id = $request->template_id;
            $campaign->device_id       = $request->device_id;
            $campaign->campaign_type   = TypeEnum::WEB;
            $campaign->media_url       = $media_url;

            // check below two
            $campaign->schedule_at  = $request->schedule_time ?? null;

            if ($request->contact_list_id !== 'all' && isset($request->contact_list_id)) {
                $contactListIds = is_array($request->contact_list_id) ? $request->contact_list_id : [$request->contact_list_id];
                $campaign->contact_list_ids = $contactListIds;
            }
            if ($request->segment_id !== 'all' && isset($request->segment_id)) {
                $segmentIds = is_array($request->segment_id) ? $request->segment_id : [$request->segment_id];
                $campaign->segment_ids = $segmentIds;
            }
            $campaign->save();


            // Prepare contacts
            $contacts = $this->contact->select('contacts.*')
                ->where('contacts.status', 1)
                ->where('contacts.is_blacklist', 0)
                ->where('contacts.type', TypeEnum::WHATSAPP)
                ->whereNotNull('contacts.phone')
                ->where('contacts.client_id', $client->id);

            if (!empty($request->contact_list_id) && $request->contact_list_id !== 'all') {
                $contactListIds = is_array($request->contact_list_id) ? $request->contact_list_id : [$request->contact_list_id];
                $contacts->join('contact_relation_lists', 'contact_relation_lists.contact_id', '=', 'contacts.id')
                    ->whereIn('contact_relation_lists.contact_list_id', $contactListIds);
            }

            if (!empty($request->segment_id) && $request->segment_id !== 'all') {
                $segmentIds = is_array($request->segment_id) ? $request->segment_id : [$request->segment_id];
                $contacts->join('contact_relation_segments', 'contact_relation_segments.contact_id', '=', 'contacts.id')
                    ->whereIn('contact_relation_segments.segment_id', $segmentIds);
            }

            $contacts                = $contacts->distinct()->get();
            // Determine scheduled time (timezone-aware)
            if ($request->send_scheduled == 1 && !empty($request->schedule_time)) {
                $clientTimezone  = $client->timezone ?? config('app.timezone');
                $systemTimezone  = config('app.timezone');
                $scheduleTime    = Carbon::createFromFormat('Y-m-d H:i', $request->schedule_time, $clientTimezone)
                    ->setTimezone($systemTimezone);
            } else {
                $scheduleTime = Carbon::now();
            }

            // Build messages for each contact (like ContactTemplateStore)
            $delaySeconds = 0;

            foreach ($contacts as $contact) {
                $header_image   = null;
                $header_document = null;
                $header_video   = null;
                $header_audio   = null;
                $content        = $template->message;

                // determine header media type based on template->message_type
                if ($template->message_type == 'image') {
                    $header_image = $template->media_url;
                } elseif ($template->message_type == 'document') {
                    $header_document = $template->media_url;
                } elseif ($template->message_type == 'video') {
                    $header_video = $template->media_url;
                } elseif ($template->message_type == 'audio') {
                    $header_audio = $template->media_url;
                }

                // Create message record
                $message = new $this->message();
                $message->contact_id        = $contact->id;
                $message->web_template_id   = $template->id;
                $message->client_id         = $client->id;
                $message->campaign_id       = $campaign->id;
                $message->is_campaign_msg   = 1;
                $message->header_text       = null;
                $message->footer_text       = null;
                $message->header_image      = $header_image;
                $message->header_audio      = $header_audio;
                $message->header_video      = $header_video;
                $message->header_location   = null;
                $message->header_document   = $header_document;
                $message->buttons           = null;
                $message->value             = $content;
                $message->error             = null;
                $message->message_type      = $template->message_type;
                $message->source            = TypeEnum::WEB;
                $message->status            = MessageStatusEnum::SCHEDULED;
                $message->schedule_at       = $scheduleTime;
                $message->components        = null;
                $message->component_header  = null;
                $message->component_body    = null;
                $message->component_buttons = null;
                $message->save();

                // Update campaign contact count
                $campaign->increment('total_contact');

                // Update conversation and dispatch message job
                $this->conversationWebUpdate($client->id, $contact->id);

                // SendWhatsAppWebCampaignMessageJob::dispatch($message)
                //     ->delay($scheduleTime);

                $jobDelay = $scheduleTime->copy()->addSeconds($delaySeconds);

                SendWhatsAppWebCampaignMessageJob::dispatch($message)
                    ->delay($jobDelay);

                $delaySeconds += 10;
            }

            DB::commit();

            return $this->formatResponse(true, __('created_successfully'), 'client.web.whatsapp.campaigns.index', []);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('WhatsApp Campaign Error: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.web.whatsapp.campaigns.index', []);
        }
    }

    public function ContactTemplateStore($request)
    {
        DB::beginTransaction();
        try {
            
            $client = auth()->user()->client;

            $contact                    = $this->contact->findOrFail($request->contact_id);
            // $template                   = $this->template->findOrFail($request->template_id);
            $template = WebTemplate::find($request->template_id);
            if (!$template) {
                return $this->formatResponse(false, __('invalid_template_selected'), 'client.web.whatsapp.campaigns.index', []);
            }

            $activeSubscription = $client->activeSubscription;
            if (!$activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.web.whatsapp.campaigns.index', []);
            }

            $content                    = $template->message;
            $header_image               = null;
            $header_document            = null;
            $header_video               = null;
            $header_audio               = null;

            if ($template->message_type == 'image') {
                $header_image = $template->media_url;
            } elseif ($template->message_type == 'document') {
                $header_document = $template->media_url;
            } elseif ($template->message_type == 'video') {
                $header_video = $template->media_url;
            } elseif ($template->message_type == 'audio') {
                $header_audio = $template->media_url;
            }

            $message                    = new $this->message();
            $message->contact_id        = $contact->id;
            $message->web_template_id   = $template->id;
            $message->client_id         = Auth::user()->client->id;
            $message->header_text       = null;
            $message->footer_text       = null;
            $message->header_image      = $header_image;
            $message->header_audio      = $header_audio;
            $message->header_video      = $header_video;
            $message->header_location   = null;
            $message->header_document   = $header_document;
            $message->buttons           = null;
            $message->value             = $content;
            $message->error             = null;
            $message->message_type      = $template->message_type;
            $message->source            = TypeEnum::WEB;
            $message->status            = MessageStatusEnum::SCHEDULED;
            $scheduleTime               = Carbon::now();
            $message->schedule_at       = $scheduleTime;
            $message->components        = null;
            $message->component_header  = null;
            $message->component_body    = null;
            $message->component_buttons = null;
            $message->campaign_id       = null;
            $message->is_campaign_msg   = 1;
            $message->save();
            $this->conversationWebUpdate(Auth::user()->client_id, $contact->id);
            SendWhatsAppWebCampaignSingleContactMessageJob::dispatch($message);
            DB::commit();
            return $this->formatResponse(true, __('created_successfully'), 'client.web.chat.index', []);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Send Template: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.chat.index', []);
        }
    }

    public function find($id)
    {
        return Campaign::find($id);
    }

    // public function sendScheduleMessage($request)
    // {
    //     $limit    = setting('message_limit') ? (int) setting('message_limit') : 100;
    //     // Retrieve scheduled messages that are ready to be sent
    //     $messages = $this->message
    //         ->where('status', MessageStatusEnum::SCHEDULED)
    //         ->where('schedule_at', '<', now())
    //         ->whereHas('campaign', function ($query) {
    //             $query->where('status', StatusEnum::ACTIVE)
    //                 ->orWhere('status', StatusEnum::PROCESSED)
    //                 ->orWhere('status', StatusEnum::EXECUTED);
    //         })
    //         ->take($limit)
    //         ->get();

    //     // Process each scheduled message
    //     foreach ($messages as $message) {
    //         if ($message->source == TypeEnum::WHATSAPP) {
    //             SendWhatsAppCampaignMessageJob::dispatch($message);
    //         } else {
                
    //             $this->sendTelegramMessage($message, $message->message_type);
    //         }
    //     }
    //     $this->updateCampaignStatus();
    //     return true;
    // }

    public function sendScheduleMessage($request)
    {
        $limit = setting('message_limit') ? (int) setting('message_limit') : 100;

        DB::beginTransaction();
        try {
            $messageIds = $this->message
                ->where('status', MessageStatusEnum::SCHEDULED)
                ->where('schedule_at', '<', now())
                ->whereHas('campaign', function ($query) {
                    $query->whereIn('status', [
                        StatusEnum::ACTIVE,
                        StatusEnum::PROCESSED,
                        StatusEnum::EXECUTED
                    ]);
                })
                ->orderBy('schedule_at')
                ->limit($limit)
                ->lockForUpdate()
                ->pluck('id')
                ->toArray();

            $this->message
                ->whereIn('id', $messageIds)
                ->update(['status' => MessageStatusEnum::PROCESSING]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logError('Failed to lock messages for sending: ', $e);
            return false;
        }

        // Eager-load relationships to prevent N+1
        $messages = $this->message
            ->with(['campaign', 'contact'])
            ->whereIn('id', $messageIds)
            ->get();

        // Dispatch each job asynchronously
        foreach ($messages as $message) {
            if ($message->source == TypeEnum::WHATSAPP) {
                SendWhatsAppWebCampaignMessageJob::dispatch($message);
            } else {
                $this->sendTelegramMessage($message, $message->message_type);
            }
        }

        $this->updateCampaignStatus();
        return true;
    }




    private function updateCampaignStatus()
    {
        $campaigns = Campaign::whereIn('status', [StatusEnum::ACTIVE, StatusEnum::PROCESSED])->get();
        foreach ($campaigns as $campaign) {
            $nonScheduledMessagesExist = $campaign->messages()->where('status', '!=', MessageStatusEnum::SCHEDULED)->exists();
            if ($nonScheduledMessagesExist) {
                DB::table('campaigns')->where('id', $campaign->id)->update([
                    'status' => StatusEnum::EXECUTED,
                ]);
            }
        }
    }

    public function statusUpdate($request, $id)
    {
        try {
            $campaign         = $this->model->find($id);
            $campaign->status = $request->status;
            $campaign->save();

            return $this->formatResponse(true, __('updated_successfully'), 'client.web.whatsapp.campaigns.index', $campaign);
        } catch (\Throwable $e) {
            return $this->formatResponse(false, $e->getMessage(), 'client.web.whatsapp.campaigns.index', []);
        }
    }

    //Resend
    public function resend($request)
    {
        DB::beginTransaction();

        try {
            $client = auth()->user()->client;

            $campaign = $this->model->findOrFail($request->campaign_id);
            $campaign->status = StatusEnum::ACTIVE;
            $campaign->save();

            // Filter messages by resend option
            $messages = $this->message->where('campaign_id', $campaign->id);
            switch ($request->resend_option) {
                case 'did_not_read':
                    $messages->where('status', MessageStatusEnum::DELIVERED);
                    break;
                case 'not_delivered':
                    $messages->where('status', MessageStatusEnum::SENT);
                    break;
                default:
                    $messages->where('status', MessageStatusEnum::FAILED);
            }

            $messages = $messages->get();

            if ($messages->isEmpty()) {
                return $this->formatResponse(false, __('no_messages_found_to_resend'), 'client.web.whatsapp.campaigns.index', []);
            }

            $delaySeconds = 0;

            foreach ($messages as $message) {
                $newMessage = new $this->message();
                $newMessage->fill([
                    'contact_id'        => $message->contact_id,
                    'web_template_id'   => $message->web_template_id,
                    'client_id'         => $message->client_id,
                    'header_text'       => $message->header_text,
                    'footer_text'       => $message->footer_text,
                    'header_image'      => $message->header_image,
                    'header_audio'      => $message->header_audio,
                    'header_video'      => $message->header_video,
                    'header_location'   => $message->header_location,
                    'header_document'   => $message->header_document,
                    'buttons'           => $message->buttons,
                    'value'             => $message->value,
                    'error'             => null,
                    'message_type'      => $message->message_type,
                    'status'            => MessageStatusEnum::SCHEDULED,
                    'components'        => $message->components,
                    'component_header'  => $message->component_header,
                    'component_body'    => $message->component_body,
                    'component_buttons' => $message->component_buttons,
                    'campaign_id'       => $message->campaign_id,
                    'is_campaign_msg'   => 1,
                ]);

                $jobDelay = Carbon::now()->copy()->addSeconds($delaySeconds);
                $newMessage->schedule_at = $jobDelay;
                $newMessage->save();

                // Update conversation limit
                $this->conversationUpdate($client->id, $newMessage->contact_id);

                // Dispatch new message job
                SendWhatsAppWebCampaignMessageJob::dispatch($newMessage)
                    ->delay($jobDelay);

                $delaySeconds += 10;
            }

            // Update total contact count once
            $campaign->increment('total_contact', $messages->count());

            DB::commit();

            return $this->formatResponse(true, __('created_successfully'), 'client.web.whatsapp.campaigns.index', []);

        } catch (\Throwable $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Resend Campaign: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.web.whatsapp.campaigns.index', []);
        }
    }

    // public function resend($request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $client = auth()->user()->client;

    //         $campaign              = $this->model->findOrFail($request->campaign_id);
    //         $campaign->status      = StatusEnum::ACTIVE;
    //         $campaign->save();
    //         $messages              = $this->message->where('campaign_id', $campaign->id);
    //         if ($request->resend_option == 'did_not_read') {
    //             $messages = $messages->where('status', MessageStatusEnum::DELIVERED);
    //         } elseif ($request->resend_option == 'not_delivered') {
    //             $messages = $messages->where('status', MessageStatusEnum::SENT);
    //         } else {
    //             $messages = $messages->where('status', MessageStatusEnum::FAILED);
    //         }
    //         $messages              = $messages->get();
    //         if ($messages->isEmpty()) {
    //             return $this->formatResponse(false, __('no_messages_found_to_resend'), 'client.web.whatsapp.campaigns.index', []);
    //         }

    //         $delaySeconds = 0;

    //         foreach ($messages as $message) {
    //             $newMessage                    = new $this->message();
    //             $newMessage->contact_id        = $message->contact_id;
    //             $newMessage->web_template_id   = $message->web_template_id;
    //             $newMessage->client_id         = $message->client_id;
    //             $newMessage->header_text       = $message->header_text;
    //             $newMessage->footer_text       = $message->footer_text;
    //             $newMessage->header_image      = $message->header_image;
    //             $newMessage->header_audio      = $message->header_audio;
    //             $newMessage->header_video      = $message->header_video;
    //             $newMessage->header_location   = $message->header_location;
    //             $newMessage->header_document   = $message->header_document;
    //             $newMessage->buttons           = $message->buttons;
    //             $newMessage->value             = $message->value;
    //             $newMessage->error             = null;
    //             $newMessage->message_type      = $message->message_type;
    //             $newMessage->status            = MessageStatusEnum::SCHEDULED;
    //             $newMessage->schedule_at       = Carbon::now();
    //             $newMessage->components        = $message->components;
    //             $newMessage->component_header  = $message->component_header;
    //             $newMessage->component_body    = $message->component_body;
    //             $newMessage->component_buttons = $message->component_buttons;
    //             $newMessage->campaign_id       = $message->campaign_id;
    //             $newMessage->is_campaign_msg   = 1;
    //             $newMessage->save();
    //             $campaign->total_contact += 1;
    //             $campaign->save();
    //             $this->conversationUpdate(Auth::user()->client->id, $newMessage->contact_id);

    //             $jobDelay = Carbon::now()->copy()->addSeconds($delaySeconds);

    //             SendWhatsAppWebCampaignMessageJob::dispatch($message)
    //                 ->delay($jobDelay);

    //             $delaySeconds += 10;
    //         }

    //         DB::commit();

    //         return $this->formatResponse(true, __('created_successfully'), 'client.web.whatsapp.campaigns.index', []);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         if (config('app.debug')) {
    //             dd($e->getMessage());            
    //         }
    //         logError('Resend Camapign: ', $e);
    //         return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.web.whatsapp.campaigns.index', []);
    //     }
    // }
 
    public function campaignCountContact($request)
    {
        $contacts = $this->contact->select('contacts.*')
            ->active()
            ->where('contacts.is_blacklist', 0)
            ->where('type', TypeEnum::WHATSAPP)
            ->whereNotNull('phone')
            ->where('client_id', Auth::user()->client->id);
        if (!empty($request->contact_list_id)) {
            $contactListIds = is_array($request->contact_list_id) ? $request->contact_list_id : [$request->contact_list_id];
            $contacts = $contacts->join('contact_relation_lists', 'contact_relation_lists.contact_id', '=', 'contacts.id')
                ->whereIn('contact_relation_lists.contact_list_id', $contactListIds);
        }
        if (!empty($request->segment_id)) {
            $segmentIds = is_array($request->segment_id) ? $request->segment_id : [$request->segment_id];
            $contacts = $contacts->join('contact_relation_segments', 'contact_relation_segments.contact_id', '=', 'contacts.id')
                ->whereIn('contact_relation_segments.segment_id', $segmentIds);
        }
        // Get the count of distinct contacts
        $contacts = $contacts->distinct('contacts.id');
        $count = $contacts->count();
        return $count;
    }
    
    
    
}
