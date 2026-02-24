<?php

namespace App\Http\Controllers\Client\Web;

use App\Models\BotReply;
use App\Traits\RepoResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\DataTables\Client\Web\WarmUpDataTable;
use App\DataTables\Client\Web\WarmUpDetailsDataTable;
use App\DataTables\Client\Web\WarmUpMessageDataTable;
use App\Http\Requests\Client\Web\WarmUpDeviceRequest;
use App\Http\Requests\Client\Web\WarmUpNumberRequest;
use App\Http\Requests\Client\Web\WarmUpRequest;
use App\Jobs\WhatsAppWarmupJob;
use App\Models\Device;
use App\Models\WarmupContact;
use App\Models\WhatsAppWarmupMessage;
use App\Repositories\Client\Web\WarmUpRepository;

class WarmUpController extends Controller
{
    use RepoResponse;

    protected $warmUp;

    public function __construct(WarmUpRepository $warmUp)
    {
        $this->warmUp = $warmUp;
    }

    public function index(WarmUpDataTable $warmUpDataTable)
    {
        return $warmUpDataTable->render('backend.client.web.warm_up.index');
    }

    public function create()
    {
        $client = auth()->user()->client;
        $devices = Device::where('client_id', $client->id)->orderBy('id', 'DESC')->get();
        return view('backend.client.web.warm_up.create', compact('devices'));
    }

    public function store(WarmUpRequest $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }
  
        DB::beginTransaction();
        try {
            $this->warmUp->store($request);
            DB::commit();
            Toastr::success(__('create_successful'));
            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('client.web.whatsapp.warm-up.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    } 

    // public function edit($id)
    // {
    //     $warmUp = $this->warmUp->find($id);
    //     $client = auth()->user()->client;
    //     $devices = Device::where('client_id', $client->id)->orderBy('id', 'DESC')->get();
    //     return view('backend.client.web.warm_up.edit', compact('warmUp','devices'));
    // }
    public function edit($id)
    {
        $client = auth()->user()->client;
        $warmUp = $this->warmUp->find($id);

        if (!$warmUp || $warmUp->client_id != $client->id) {
            abort(404, __('not_found'));
        }

        $devices = Device::where('client_id', $client->id)->orderBy('id', 'DESC')->get();

        // Get helper devices related to this warmup
        $selectedHelpers = WarmupContact::where('warmup_id', $id)->pluck('device_id')->toArray();

        return view('backend.client.web.warm_up.edit', compact('warmUp', 'devices', 'selectedHelpers'));
    }

    public function update(WarmUpRequest $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            $this->warmUp->update($request, $id);
            DB::commit();
            Toastr::success(__('update_successful'));

            return response()->json([
                'success' => __('update_successful'),
                'route'   => route('client.web.whatsapp.warm-up.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function destroy($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->warmUp->destroy($id);
            Toastr::success(__('delete_successful'));
            $data = [
                'status'  => 'success',
                'message' => __('delete_successful'),
                'title'   => __('success'),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function statusChange(Request $request): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 400,
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->warmUp->statusChange($request->all());
            $data = [
                'status'  => 200,
                'message' => __('update_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }            
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'danger',
            ];

            return response()->json($data);
        }
    }

    // public function manage(WarmUpMessageDataTable $dataTable, $id)
    // {
    //     $warmUp = $this->warmUp->find($id);
    //     $client = auth()->user()->client;
    //     $totalWarmUpContact       = WarmupContact::where('warmup_id', $warmUp->id)->where('device_id', NULL)->count();
    //     $totalWarmUpDeviceContact = WarmupContact::where('warmup_id', $warmUp->id)->whereNotNull('device_id')->count();
    //     $totalWarmUpMessages      = WhatsAppWarmupMessage::where('warmup_id', $id)->count();

    //     $wdc = WarmupContact::where('warmup_id', $warmUp->id)->get();
    //     $deviceGet = Device::where('client_id', $client->id)
    //         ->whereIn('id', $wdc->device_id)
    //         ->orderBy('id', 'DESC')
    //         ->get();


    //     dd($deviceGet);

    //     return $dataTable->with('warmupId', $id)
    //         ->render('backend.client.web.warm_up.column.manage', compact('warmUp', 'totalWarmUpDeviceContact', 'totalWarmUpContact', 'totalWarmUpMessages'));
    // }
    public function manage(WarmUpMessageDataTable $dataTable, $id)
    {
        $warmUp = $this->warmUp->find($id);
        $client = auth()->user()->client;

        // Total contacts without device
        $totalWarmUpContact = WarmupContact::where('warmup_id', $warmUp->id)
            ->whereNull('device_id')
            ->count();

        // Total contacts with device
        $totalWarmUpDeviceContact = WarmupContact::where('warmup_id', $warmUp->id)
            ->whereNotNull('device_id')
            ->count();

        // Total warmup messages count
        $totalWarmUpMessages = WhatsAppWarmupMessage::where('warmup_id', $warmUp->id)->count();

        // Get all warmup contacts
        $wdc = WarmupContact::where('warmup_id', $warmUp->id)->get();

        // Extract unique device IDs
        $deviceIds = $wdc->pluck('device_id')->filter()->unique();

        // Fetch devices belonging to this client
        $warmUpDevices = Device::where('client_id', $client->id)
            ->whereIn('id', $deviceIds)
            ->orderBy('id', 'DESC')
            ->get();

        // dd($warmUpDevices);

        return $dataTable
            ->with('warmupId', $id)
            ->render('backend.client.web.warm_up.column.manage', compact(
                'warmUp',
                'totalWarmUpDeviceContact',
                'totalWarmUpContact',
                'totalWarmUpMessages',
                'warmUpDevices'
            ));
    }


    public function warmUpNumberCreate($id){
        $warmUp = $this->warmUp->find($id);
        $client = auth()->user()->client;
        $devices = Device::where('client_id', $client->id)->orderBy('id', 'DESC')->get();
        return view('backend.client.web.warm_up.number.create', compact('warmUp','devices'));
    }

    public function warmUpNumberStore(WarmUpNumberRequest $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }
  
        DB::beginTransaction();
        try {
            $this->warmUp->warmUpNumberStore($request);
            DB::commit();
            Toastr::success(__('create_successful'));
            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('client.web.whatsapp.warm-up.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    } 

    public function warmUpDeviceCreate($id){
        $warmUp = $this->warmUp->find($id);
        $client = auth()->user()->client;
        $devices = Device::where('client_id', $client->id)->orderBy('id', 'DESC')->get();
        return view('backend.client.web.warm_up.device.create', compact('warmUp','devices'));
    }

    public function warmUpDeviceStore(WarmUpDeviceRequest $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }
  
        DB::beginTransaction();
        try {
            $this->warmUp->warmUpDeviceStore($request);
            DB::commit();
            Toastr::success(__('create_successful'));
            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('client.web.whatsapp.warm-up.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    } 

    public function runNow()
    {
        WhatsAppWarmupJob::dispatch();
        return redirect()->back()->with('success', 'Warm-up job triggered!');
    }
}
