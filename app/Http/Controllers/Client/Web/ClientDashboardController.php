<?php

namespace App\Http\Controllers\Client\Web;

use App\Enums\MessageStatusEnum;
use App\Enums\TypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Timezone;
use App\Models\User;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\UserRepository;
use App\Services\NewContactsService;
use App\Services\TelegramCampaignService;
use App\Services\TelegramMessageService;
use App\Services\TotalContactsService;
use App\Services\Web\WhatsAppCampaignService as WebWhatsAppCampaignService;
use App\Services\Web\WhatsAppMessageService as WebWhatsAppMessageService;
use App\Services\WhatsAppMessageService;
use App\Traits\SendMailTrait;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientDashboardController extends Controller
{
    use SendMailTrait;

    protected $emailTemplate;

    public function __construct(EmailTemplateRepository $emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    public function index(Request $request)
    {
        $client             = auth()->user()->client;
        $activeSubscription = $client->activeSubscription;
        $total_team         = User::where('client_id', $client->user->client_id)->where('status', 1)->count();
        $total_contacts     = Contact::where('client_id', $client->user->client_id)->active()->count();
        $delivered_message  = Message::where('client_id', $client->user->client_id)->where('source', TypeEnum::WEB)->whereNotNull('campaign_id')->whereIn('status', [MessageStatusEnum::DELIVERED, MessageStatusEnum::READ])->count();
        $read_message       = Message::where('client_id', $client->user->client_id)->where('source', TypeEnum::WEB)->whereNotNull('campaign_id')->where('status', MessageStatusEnum::READ)->count();
        $subscription       = Auth::user()->client->activeSubscription;
        $data               = app(TotalContactsService::class)->execute($request);
        $totalCampaign      = Message::where('client_id', $client->user->client_id)->where('source', TypeEnum::WEB)->whereNotNull('campaign_id')->whereIn('status', [MessageStatusEnum::DELIVERED, MessageStatusEnum::READ])->count();
        if (isDemoMode()) {
            $data = [
                'client'              => $client,
                'active_subscription' => $client->activeSubscription,
                'charts'              => [
                    'labels'                => $data['labels'],
                    'total_contacts'        => [52, 100, 115, 118, 187, 275, 334, 444, 544, 555, 678, 787],
                    'new_contacts'          => [52, 48, 15, 18, 87, 175, 234, 344, 444, 455, 578, 687],
                    'whatsapp_campaign'     => [5, 10, 5, 3, 5, 0, 8, 10, 5, 3, 5, 2],
                    'telegram_campaign'     => [2, 4, 2, 3, 5, 5, 8, 10, 5, 3, 5, 8],
                    'whatsapp_conversation' => [750, 785, 15, 18, 87, 175, 234, 344, 444, 455, 578, 687],
                    'telegram_conversation' => [650, 485, 25, 88, 8, 15, 334, 344, 424, 355, 578, 587],
                ],
                'usages'              => [
                    'team'         => 1,
                    'campaign'     => 5,
                    'contact'      => 524,
                    'conversation' => 3658,
                ],
                'read_rate'           => 98.25,
            ];
        } else {
            $data = [
                'client'              => $client,
                'active_subscription' => $activeSubscription,
                'charts'              => [
                    'labels'                => $data['labels'],
                    'total_contacts'        => $data['data'],
                    'new_contacts'          => app(NewContactsService::class)->execute($request),
                    'whatsapp_campaign'     => app(WebWhatsAppCampaignService::class)->execute($request),
                    'telegram_campaign'     => app(TelegramCampaignService::class)->execute($request),
                    'whatsapp_conversation' => app(WebWhatsAppMessageService::class)->execute($request),
                    'telegram_conversation' => app(TelegramMessageService::class)->execute($request),
                ],
                'usages'              => [
                    'team'         => $total_team,
                    'campaign'     => $totalCampaign,
                    'contact'      => $total_contacts,
                    'conversation' => ($activeSubscription->conversation_remaining > 0) ? $activeSubscription->conversation_limit - $activeSubscription->conversation_remaining : 0,
                ],
                'read_rate'           => ($read_message != 0) ? $read_message / $delivered_message * 100 : 0,
            ];
        }

        return view('backend.client.web.overview', $data);
    }

}
