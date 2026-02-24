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
use App\Enums\VerifyNumberStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWhatsAppWebCampaignMessageJob;
use App\Jobs\WhatsAppNumberVerifyJob;
use App\Models\Device;
use App\Models\VerifyNumber;
use App\Models\WebTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VerifyNumberRepository
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
        VerifyNumber $model,
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

    public function find($id)
    {
        return VerifyNumber::find($id);
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

            $activeSubscription = $client->activeSubscription;
            if (!$activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.web.whatsapp.verify-number.index', []);
            }

            $verifyNumber = new $this->model;
            $verifyNumber->name = $request->name;
            $verifyNumber->client_id = $client->id;
            $verifyNumber->device_id = $request->device_id;

            if ($request->contact_list_id !== 'all' && isset($request->contact_list_id)) {
                $verifyNumber->contact_list_ids = (array) $request->contact_list_id;
            }

            if ($request->segment_id !== 'all' && isset($request->segment_id)) {
                $verifyNumber->segment_ids = (array) $request->segment_id;
            }

            // Filter contacts
            $contactsQuery = $this->contact->select('contacts.id')
                ->where('contacts.status', 1)
                ->where('contacts.is_blacklist', 0)
                ->where('contacts.type', TypeEnum::WHATSAPP)
                ->whereNotNull('contacts.phone')
                ->where('contacts.client_id', $client->id);

            if (!empty($request->contact_list_id) && $request->contact_list_id !== 'all') {
                $contactListIds = (array) $request->contact_list_id;
                $contactsQuery->join('contact_relation_lists', 'contact_relation_lists.contact_id', '=', 'contacts.id')
                    ->whereIn('contact_relation_lists.contact_list_id', $contactListIds);
            }

            if (!empty($request->segment_id) && $request->segment_id !== 'all') {
                $segmentIds = (array) $request->segment_id;
                $contactsQuery->join('contact_relation_segments', 'contact_relation_segments.contact_id', '=', 'contacts.id')
                    ->whereIn('contact_relation_segments.segment_id', $segmentIds);
            }

            $contactIds = $contactsQuery->distinct()->pluck('contacts.id')->toArray();

            $verifyNumber->contact_ids   = $contactIds;
            
            $verifyNumber->total_contact = count($contactIds);
            $verifyNumber->status = 'processing';
            $verifyNumber->save();

            DB::commit();

            // Dispatch background job
            // dispatch(new WhatsAppNumberVerifyJob($verifyNumber->id));

            return $this->formatResponse(true, __('verification_started_in_background'), 'client.web.whatsapp.verify-number.index', []);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            logError('WhatsApp Verify Store Error: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.web.whatsapp.verify-number.index', []);
        }
    }

    public function processScheduledVerifyNumbers()
    {
        // How many verify tasks to process per cron run
        $limit = setting('verify_batch_limit') ? (int) setting('verify_batch_limit') : 20;

        DB::beginTransaction();
        try {
            // Fetch pending verify tasks (scheduled or processing)
            $verifyIds = VerifyNumber::where('status', VerifyNumberStatusEnum::PROCESSING)
                ->where('created_at', '<=', now()) 
                ->orderBy('id')
                ->limit($limit)
                ->lockForUpdate()
                ->pluck('id')
                ->toArray();

            if (empty($verifyIds)) {
                DB::commit();
                return false;
            }

            // Update status → mark as PROCESSING
            VerifyNumber::whereIn('id', $verifyIds)
                ->update(['status' => VerifyNumberStatusEnum::PROCESSING]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logError('Failed to lock verify-number tasks: ', $e);
            return false;
        }

        // Load full objects after commit
        $verifyRecords = VerifyNumber::whereIn('id', $verifyIds)->get();

        foreach ($verifyRecords as $verify) {
            // Dispatch job for each verify-number process
            WhatsAppNumberVerifyJob::dispatch($verify->id);
        }

        // After jobs dispatched, update status
        $this->updateVerifyStatus();

        return true;
    }

    private function updateVerifyStatus()
    {
        $verifyRecords = VerifyNumber::whereIn('status', [
            VerifyNumberStatusEnum::PROCESSING,
            VerifyNumberStatusEnum::PROCESSING
        ])->get();

        foreach ($verifyRecords as $record) {
            $remaining = $record->contact_ids ? count($record->contact_ids) : 0;

            if ($remaining <= ($record->total_verify + $record->total_unverify)) {
                $record->update([
                    'status' => VerifyNumberStatusEnum::COMPLETED
                ]);
            }
        }
    }



    
    
}
