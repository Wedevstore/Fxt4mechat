<?php

namespace App\Http\Controllers\Api\Client\Web;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{

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

    public function deviceSetting($id)
    {
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

}
