<?php

namespace App\Http\Controllers\Client\Web;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\Device;
use App\Models\DeviceCredit;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{

    public function index()
    {
        $clientId = Auth::user()->client->id;

        // Get all devices for this client
        $devices = Device::with('credit')->where('client_id', $clientId)->get();

        // Count devices by status
        $statusCounts = [
            'total'      => $devices->count(),
            'pending'    => $devices->where('status', 'pending')->count(),
            'connected'  => $devices->where('status', 'connected')->count(),
            'logged_out' => $devices->where('status', 'logged_out')->count(),
            'blocked'    => $devices->where('status', 'blocked')->count(),
        ];

        return view('backend.client.web.devices.index', compact('devices', 'statusCounts'));
    }

    public function deviceSetting($id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        $data = Device::find($id);
        return view('backend.client.web.devices.setting', compact('data')); 
    }

    public function deviceChat($id)
    {
        $data = Device::find($id);
        $data->active_for_chat = 1;
        $data->active_for_chat_time = now();
        $data->update();

        return redirect()->route('client.web.chat.index');
    }

    public function allDevices()
    {
        $clientId = Auth::user()->client->id;

        $devices = Device::where('client_id', $clientId)->get();

        return response()->json([
            'success' => true,
            'message' => 'Devices retrieved successfully.',
            'data' => $devices
        ], 200);
        
    }

    public function deviceActive($id)
    {
        $data = Device::find($id);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        }

        $data->active_for_chat = 1;
        $data->active_for_chat_time = now();
        $data->save();

        return response()->json(['success' => true, 'message' => 'Device Selected successfully']);
    }

    public function storeDeviceToWeb(Request $request)
    {
        if (isDemoMode()) {
            return response()->json([
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ]);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        try {

            
            $client               = auth()->user()->client;
            $activeSubscription   = $client->activeSubscription;

            if (! $activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.contacts.index', []);
            }
            $existingdeviceCount = Device::where('client_id', $client->id)->count();
            if ($activeSubscription->device_limit != -1 && $existingdeviceCount >= $activeSubscription->device_limit) {
                return back()->with('success', 'Insufficient Ddevices Llimit.');
            }
            
            $apiKey = setting('admin_rapiwa_api');
            $webhookUrl = rtrim($request->getSchemeAndHttpHost(), '/') .
                        '/whatsapp/web/webhook/';
            $response = Http::withHeaders([
                'api_key' => $apiKey,            
                'Accept' => 'application/json',  
            ])->post('https://app.rapiwa.com/api/web/session/store', [
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'webhook'    => $webhookUrl,
            ]);

            if ($response->failed()) {
                return back()->with('error', 'Failed to create WhatsApp session.');
            }

            $sessionData = $response->json('data');

            $device = Device::create([
                'client_id'        => Auth::user()->client_id ?? null,
                'name'             => $sessionData['name'],
                'phone_number'     => $sessionData['phone_number'],
                'whatsapp_session' => $sessionData['whatsapp_session'],
                'webhook_url'      => $sessionData['webhook_url'],
                'status'           => $sessionData['status'] ?? 'pending',
                'jid'              => null,
                'active_for_chat'    => 1,
                'active_for_chat_time' => now(),
            ]);

            $perDeviceLimit = (int) $activeSubscription->per_device_credit_limit;

            DeviceCredit::create([
                'device_id'     => $device->id,
                'total_credits' => $perDeviceLimit,
                'used_credits'  => 0,
            ]);

            CreditTransaction::create([
                'client_id'      => $client->id,
                'device_id'      => $device->id,
                'type'           => 'credit',
                'credits'        => 0,
                'message_type'   => 'system',
                'reference'      => 'device_create',
                'balance_after'  => $perDeviceLimit,
                'description'    => 'Initial credits assigned on device creation',
            ]);

            return back()->with('success', 'Device created successfully.');

        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


}
