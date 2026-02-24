<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Contact;
use App\Models\SMSHistory;
use App\Models\SMSCampaign;
use App\Models\SMSTemplate;
use Illuminate\Http\Request;
use App\Traits\WhatsAppTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\Webhook\WhatsappRepository;
use App\Repositories\Client\WaCampaignRepository;
use App\Repositories\Client\Web\VerifyNumberRepository;

class CronController extends Controller
{
    use WhatsAppTrait;
    protected $campaign;
    protected $verifyRepo;
    protected $whatsappRepo;
    protected $settingRepo;
    protected $smsCampaignRepo;

    public function __construct(
        WaCampaignRepository $campaign,
        VerifyNumberRepository $verifyRepo,
        WhatsappRepository $whatsappRepo,
        SettingRepository $settingRepo,
    ) {
        $this->campaign             = $campaign;
        $this->verifyRepo             = $verifyRepo;
        $this->whatsappRepo         = $whatsappRepo;
        $this->settingRepo          = $settingRepo;
       
    }

    public function index($key, Request $request, SMSCampaign $model, SMSHistory $details, Contact $contact, SMSTemplate $template)
    {
        if ($key !== setting('cron_key')):
            dd(__('invalid_cron_key'));
        endif;
        try {
            $this->campaign->sendScheduleMessage($request);
            $this->verifyRepo->processScheduledVerifyNumbers();
            if (addon_is_activated('sms_marketing')) {
                $smsCampaignRepo = new \App\Addons\SMSMarketing\Repository\SMSCampaignRepository($model, $details, $contact, $template);
                $smsCampaignRepo->sendScheduleSMS();
                $smsCampaignRepo->checkSMSStatus();
            }
            $request['last_cron_run_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $this->settingRepo->update($request);
            $this->deleteAbandonContactFlow();
            Artisan::call('all:clear');
            echo __('run_successfully');
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again', 'Error!');
            return back();
        }
    }

    public function manual_run(Request $request, SMSCampaign $model, SMSHistory $details, Contact $contact, SMSTemplate $template)
    {
        try {
            $this->campaign->sendScheduleMessage($request);
            $this->verifyRepo->processScheduledVerifyNumbers();
            if (addon_is_activated('sms_marketing')) {
                $smsCampaignRepo = new \App\Addons\SMSMarketing\Repository\SMSCampaignRepository($model, $details, $contact, $template);
                $smsCampaignRepo->sendScheduleSMS();
                $smsCampaignRepo->checkSMSStatus();
            }
            $request['last_cron_run_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $this->settingRepo->update($request);
            $this->deleteAbandonContactFlow();
            Artisan::call('all:clear');
            Toastr::success(__('run_successfully'));
            return back();
        } catch (\Exception $e) {
            dd($e->getMessage());
            Toastr::error('something_went_wrong_please_try_again', 'Error!');
            return back();
        }
    }


    public function deleteAbandonContactFlow()
    {
        return DB::table('contact_flow_states')
            ->where('created_at', '<', Carbon::now()->subMinutes(15))
            ->delete();
    }
}
