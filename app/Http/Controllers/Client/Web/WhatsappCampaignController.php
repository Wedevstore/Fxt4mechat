<?php
namespace App\Http\Controllers\Client\Web;
use App\Enums\TypeEnum;
use App\Models\Timezone;
use Illuminate\Http\Request;
use App\Services\TemplateService;
use App\Services\WhatsAppService;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\DataTables\Client\MessageDataTable;
use App\DataTables\Client\Web\WhatsAppCampaignDataTable as WebWhatsAppCampaignDataTable;
use App\Services\WhatsAppNewContactsService;
use App\Http\Requests\Client\CampaignsRequest;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\SegmentRepository;
use App\Services\WhatsAppTotalContactsService;
use App\Repositories\Client\CampaignRepository;
use App\Repositories\Client\TemplateRepository;
use App\Repositories\Client\Web\WaCampaignRepository;
use App\Repositories\Client\ContactListRepository;
use App\Http\Requests\Client\ResendCampaignRequest;
use App\DataTables\Client\WhatsAppCampaignDataTable;
use App\Http\Requests\Client\Web\CampaignsRequest as WebCampaignsRequest;
use App\Repositories\Client\Web\TemplateRepository as WebTemplateRepository;
use App\Services\WebTemplateService;

class WhatsappCampaignController extends Controller
{
    protected $repo;

    protected $templateRepo;

    protected $contactListsRepo;

    protected $ContactsRepo;

    protected $segmentsRepo;

    protected $campaignsRepo;
    
    protected $whatsappService;

    public function __construct(
        WaCampaignRepository $repo,
        WebTemplateRepository $templateRepo,
        ContactListRepository $contactListsRepo,
        ContactRepository $ContactsRepo,
        SegmentRepository $segmentsRepo,
        CampaignRepository $campaignsRepo,
        WhatsAppService $whatsappService

    ) {
        $this->repo             = $repo;
        $this->templateRepo     = $templateRepo;
        $this->contactListsRepo = $contactListsRepo;
        $this->ContactsRepo     = $ContactsRepo;
        $this->segmentsRepo     = $segmentsRepo;
        $this->campaignsRepo    = $campaignsRepo;
        $this->whatsappService = $whatsappService;

    }

    public function index(Request $request, WebWhatsAppCampaignDataTable $dataTable)
    {
        $data = [
            'templates' => $this->templateRepo->combo(),
            'segments'  => $this->segmentsRepo->combo(),
            'lists'     => $this->contactListsRepo->combo(),
        ];

        return $dataTable->render('backend.client.web.campaigns.index', $data);
    }

    public function create()
    {
        $data = [
            'devices'       => $this->repo->allDevice(),
            'templates'     => $this->templateRepo->combo(),
            'segments'      => $this->segmentsRepo->combo(),
            'contact_lists' => $this->contactListsRepo->combo(),
            'time_zones'    => Timezone::all(),

        ];

        return view('backend.client.web.campaigns.create', $data);

    }

    public function store(WebCampaignsRequest $request): \Illuminate\Http\RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        $result = $this->repo->store($request);
        if ($result->status) {
            return redirect()->route($result->redirect_to)->with($result->redirect_class, $result->message);
        }
        return back()->with($result->redirect_class, $result->message);
    }

    public function storeContactTemplate(Request $request): \Illuminate\Http\RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        
        $result = $this->repo->ContactTemplateStore($request);

        if ($result->status) {
            return redirect()->route('client.web.chat.index', ['contact' => $request->contact_id])->with($result->redirect_class, $result->message);
        }

        return back()->with($result->redirect_class, $result->message);
    }

    public function overview(Request $request)
    {

        $modifiedRequest = new Request(array_merge($request->all(), ['type' => TypeEnum::WHATSAPP->value]));
        $totalContacts   = Auth::user()->client->contacts()->where('type', TypeEnum::WHATSAPP->value)->count();
        $activeContacts  = Auth::user()->client->contacts()->where('type', TypeEnum::WHATSAPP->value)->active()->count();

        if ($totalContacts > 0) {
            $activePercentage = ($activeContacts / $totalContacts) * 100;
        } else {
            $activePercentage = 0;
        }

        $data            = [
            'charts'             => [
                'total_contacts' => app(WhatsAppTotalContactsService::class)->execute($request),
                'new_contacts'   => app(WhatsAppNewContactsService::class)->execute($request),
            ],
            'allContact'         => $totalContacts,
            'blacklistCount'     => $this->ContactsRepo->blockContacts($modifiedRequest)->count(),
            'readRatePercentage' => $this->ContactsRepo->readRatePercentage($modifiedRequest),
            'activePercentage'   => $activePercentage,
        ];
        return view('backend.client.whatsapp.overview.index', $data);
    }

    public function campaignCountContact(Request $request)
    {

    return $this->repo->campaignCountContact($request);
    }

    public function statusUpdate(Request $request, $id)
    {
        return $this->repo->statusUpdate($request, $id);
    }

    public function view(MessageDataTable $dataTable, $id)
    {
        try {
            $campaign = $this->repo->find($id);

            $data     = [
                'campaign' => $campaign,
            ];

            return $dataTable->with('id', $id)->render('backend.client.web.campaigns.view', $data);
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return back();
        }
    }

    //Resend Campaign
    public function resend(ResendCampaignRequest $request): \Illuminate\Http\RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        $result = $this->repo->resend($request);
        if ($result->status) {
            return redirect()->route($result->redirect_to)->with($result->redirect_class, $result->message);
        }
        return back()->with($result->redirect_class, $result->message);
    }

    public function sendTemplate(Request $request)
    {
        try {
            $data =[];
            $template  = $this->templateRepo->find($request->template_id);
            // $data = app(WebTemplateService::class)->execute($template);
            $data = $template;
            $contact_id = $request->contact_id;
            // dd($data);
            return view('backend.client.web.campaigns.contact_template', compact('data', 'contact_id'));
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            return back();
        }
    }
}
