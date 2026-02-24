<?php
namespace App\Http\Controllers\Client\Web;
use App\Models\Language;
use App\Traits\RepoResponse;
use Illuminate\Http\Request;
use App\Services\TemplateService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TemplateResource;
use App\Repositories\Client\Web\TemplateRepository;
use App\Http\Requests\Client\TemplateStoreRequest;
use App\DataTables\Client\TelegramTemplateDataTable;
use App\DataTables\Client\Web\TemplateDataTable;
use App\Http\Requests\Client\Web\TemplateStoreRequest as WebTemplateStoreRequest;
use App\Http\Resources\WebTemplateResource;
use App\Models\Device;
use App\Models\WebTemplate;

class TemplateController extends Controller
{
    use RepoResponse;
    protected $repo;

    protected $whatsappService;

    public function __construct(TemplateRepository $repo, WhatsAppService $whatsappService)
    {
        $this->repo            = $repo;
        $this->whatsappService = $whatsappService;
    }

    public function index(TemplateDataTable $templateDataTable)
    {
        return $templateDataTable->render('backend.client.web.template.index');
    }

    public function loadTemplate(Request $request)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }

        $result = $this->repo->loadTemplate();

        if ($result->status) {
            return redirect()->route($result->redirect_to)->with($result->redirect_class, $result->message);
        }
        
        return back()->with($result->redirect_class, $result->message);
    }

    public function create()
    {
        $data['locales'] =  Language::pluck('name','locale');
        return view('backend.client.web.template.create',$data);
    }

    public function store(Request $request)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        
        $result = $this->repo->store($request);

        if ($result->status) {
            return response()->json($result, 200);
        }
        return response()->json($result, 200);
    }

    // public function edit($id)
    // {
    //     if (isDemoMode()) {
    //         Toastr::error(__('this_function_is_disabled_in_demo_server'));
    //         return back();
    //     }
    //     $row =  $this->repo->find($id);
    //     $data = app(TemplateService::class)->execute($row);

    //     return view('backend.client.web.template.edit', $data);
    // }

    public function edit($id)
    {
        $template = $this->repo->find($id);
        $data['locales'] = Language::pluck('name', 'locale');
        $data['template'] = $template;

        return view('backend.client.web.template.edit', $data);
    }

    public function getTemplateByID($id)
    {
        return $this->repo->getTemplateByID($id);
    }

    public function update(Request $request, $id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        $result = $this->repo->update($request,$id);
        if ($result->status) {
            return response()->json($result, 200);
        }
        return response()->json($result, 200);
    }

    public function delete($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];
            return response()->json($data);
        }
        return $this->repo->destroy($id);
    }

    public function whatsappTemplates(Request $request): JsonResponse
    { 
        try {
            $templates = $request->flow_builder ? $this->repo->activeWhatsappTemplate() : $this->repo->whatsappTemplate();
            $data      = [
                'webTemplates'     => WebTemplateResource::collection($templates),
                'next_page_url' => $request->flow_builder ? false : $templates->nextPageUrl(),
                'success'       => true,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'error' => $e->getMessage(),
            ];
            return response()->json($data);
        }
    }


}
