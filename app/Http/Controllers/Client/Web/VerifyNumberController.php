<?php
namespace App\Http\Controllers\Client\Web;

use App\Models\Timezone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\DataTables\Client\Web\VerifyContactDataTable;
use App\DataTables\Client\Web\VerifyNumberDataTable;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\SegmentRepository;
use App\Repositories\Client\Web\VerifyNumberRepository;
use App\Repositories\Client\ContactListRepository;
use App\Http\Requests\Client\Web\VerifyNumberRequest;
use App\Jobs\WhatsAppNumberVerifyJob;
use App\Models\VerifyNumber;

class VerifyNumberController extends Controller
{
    protected $repo;

    protected $contactListsRepo;

    protected $ContactsRepo;

    protected $segmentsRepo;

    public function __construct(
        VerifyNumberRepository $repo,
        ContactListRepository $contactListsRepo,
        ContactRepository $ContactsRepo,
        SegmentRepository $segmentsRepo,

    ) {
        $this->repo             = $repo;
        $this->contactListsRepo = $contactListsRepo;
        $this->ContactsRepo     = $ContactsRepo;
        $this->segmentsRepo     = $segmentsRepo;
    }

    public function index(VerifyNumberDataTable $dataTable)
    {
        $data = [
            'segments'  => $this->segmentsRepo->combo(),
            'lists'     => $this->contactListsRepo->combo(),
        ];

        return $dataTable->render('backend.client.web.verify-number.index', $data);
    }

    public function create()
    {
        $data = [
            'devices'       => $this->repo->allDevice(),
            'segments'      => $this->segmentsRepo->combo(),
            'contact_lists' => $this->contactListsRepo->combo(),
            'time_zones'    => Timezone::all(),
        ];

        return view('backend.client.web.verify-number.create', $data);

    }

    public function store(VerifyNumberRequest $request): \Illuminate\Http\RedirectResponse
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

    public function statusUpdate(Request $request, $id)
    {
        return $this->repo->statusUpdate($request, $id);
    }

    public function view(VerifyContactDataTable $dataTable, $id)
    {
        try {
            $verifyNumber = $this->repo->find($id);

            return $dataTable
                ->with(['verifyNumber' => $verifyNumber])
                ->render('backend.client.web.verify-number.view', [
                    'verifyNumber' => $verifyNumber,
                ]);

        } catch (\Exception $e) {
            \Log::error('Verify Number View Error: ' . $e->getMessage());
            Toastr::error(__('something_went_wrong_please_try_again'));
            return back();
        }
    }

    public function verifyNumberJob()
    {
        $client = auth()->user()->client;

        // Get all VerifyNumbers in 'processing' status for this client
        $verifyNumbers = VerifyNumber::where('client_id', $client->id)
            ->where('status', 'processing')
            ->get();

        if ($verifyNumbers->isEmpty()) {
            return back()->with('warning', __('No VerifyNumbers in processing status.'));
        }

        foreach ($verifyNumbers as $verifyNumber) {
            // Dispatch job for each record
            WhatsAppNumberVerifyJob::dispatch($verifyNumber);
        }

        return back()->with('success', __('Verification job(s) started in background.'));
    }




}
