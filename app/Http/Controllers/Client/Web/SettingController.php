<?php

namespace App\Http\Controllers\Client\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientSetting;
use App\Models\Device;
use App\Repositories\Client\Web\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    protected $repo;

    public function __construct(SettingRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setting()
    {
        return view('backend.client.web.setting.index');
    }

    public function whatsAppWebUpdate(Request $request)
    {
        if (isDemoMode()) {
            return response()->json([
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ]);
        }

        $client = Auth::user()->client;
        $clientId = $client->id;

        $request->validate([
            'url'        => 'required|string',
            'client_key' => 'required|string',
        ]);

        $updateResponse = $this->repo->webSettingUpdate($request);

        $updateData = [
            'status'         => $updateResponse->status ?? false,
            'message'        => $updateResponse->message ?? null,
            'description'    => $updateResponse->description ?? null,
            'data'           => $updateResponse->data ?? null,
            'id'             => $updateResponse->id ?? 0,
            'redirect_to'    => $updateResponse->redirect_to ?? null,
            'redirect_class' => $updateResponse->redirect_class ?? null,
        ];

        if (!$updateData['status']) {
            return response()->json([
                'status'  => false,
                'message' => $updateData['message'] ?? 'Update failed',
            ]);
        }

        $client_key = ClientSetting::where('client_id', $clientId)
            ->where('type', 'rapiwa')
            ->value('access_token');

        if (!$client_key) {
            return response()->json([
                'status'  => false,
                'message' => 'Client key missing after update'
            ], 400);
        }

        $url = $client->webSetting->app_id;

        $response = Http::get($url . '/api/devices/by-api-key', [
            'api_key' => $client_key
        ]);

        $data = $response->json();

        if (!isset($data['sessions'])) {
            return response()->json([
                'status'  => false,
                'message' => 'No sessions found during sync'
            ], 404);
        }

        $apiSessions = collect($data['sessions'])->keyBy('whatsapp_session');

        foreach ($apiSessions as $sessionKey => $session) {
            Device::updateOrCreate(
                ['whatsapp_session' => $sessionKey, 'client_id' => $clientId],
                [
                    'name'               => $session['name'] ?? null,
                    'phone_number'       => $session['phone_number'] ?? null,
                    'jid'                => $session['jid'] ?? null,
                    'status'             => $session['status'] ?? null,
                    'account_protection' => $session['account_protection'] ?? null,
                    'message_logging'    => $session['message_logging'] ?? null,
                    'read_incoming'      => $session['read_incoming'] ?? null,
                    'webhook_url'        => $session['webhook_url'] ?? null,
                    'connected_at'       => $session['connected_at'] ?? null,
                    'disconnected_at'    => $session['disconnected_at'] ?? null,
                ]
            );
        }

        $sessionsToKeep = $apiSessions->keys()->toArray();
        Device::where('client_id', $clientId)
            ->whereNotIn('whatsapp_session', $sessionsToKeep)
            ->delete();

        return response()->json([
            'status'         => true,
            'message'        => 'WhatsApp Web updated & devices synced successfully',
            'updated'        => $updateData,
            'synced_devices' => $apiSessions->values()->toArray(),
        ]);
    }



    // public function whatsAppWebUpdate(Request $request)
    // {
    //     if (isDemoMode()) {
    //         $data = [
    //             'status' => false, 
    //             'message'  => __('this_function_is_disabled_in_demo_server'),
    //         ];
    //         return response()->json($data);
    //     }
        
    //     $clientId = auth()->user()->client->id;

    //     $request->validate([
    //         'url' => 'required|string',
    //         'client_key' => 'required|string',
    //     ]);

    //     return $this->repo->webSettingUpdate($request);
    // }

    public function removeWhatsAppWeb(Request $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        return $this->repo->removeWhatsAppWebToken($request, $id);
    }

    public function whatsAppWebSync($id)
    {
        if (isDemoMode()) {
            return response()->json([
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ]);
        }
        
        $client_key = ClientSetting::where('client_id', $id)
            ->where('type', 'rapiwa')
            ->value('access_token');

        if (!$client_key) {
            return response()->json([
                'status' => false,
                'message' => 'Client not found or API key missing'
            ], 404);
        }

        // Call Rapiwa API
        $url = Auth::user()->client->webSetting->app_id;
        $response = Http::get($url . '/api/devices/by-api-key', [
            'api_key' => $client_key
        ]);

        $data = $response->json();

        if (!isset($data['sessions'])) {
            return response()->json([
                'status' => false,
                'message' => 'No sessions found'
            ], 404);
        }

        // Get existing devices for this client
        $existingDevices = Device::where('client_id', $id)->get()->keyBy('whatsapp_session');

        $apiSessions = collect($data['sessions'])->keyBy('whatsapp_session');

        // Update or create devices
        foreach ($apiSessions as $sessionKey => $session) {
            Device::updateOrCreate(
                ['whatsapp_session' => $sessionKey, 'client_id' => $id],
                [
                    'name'               => $session['name'],
                    'phone_number'       => $session['phone_number'],
                    'jid'                => $session['jid'],
                    'status'             => $session['status'],
                    'account_protection' => $session['account_protection'],
                    'message_logging'    => $session['message_logging'],
                    'read_incoming'      => $session['read_incoming'],
                    'webhook_url'        => $session['webhook_url'],
                    'connected_at'       => $session['connected_at'],
                    'disconnected_at'    => $session['disconnected_at'],
                ]
            );
        }

        // Delete devices not present in API response
        $sessionsToKeep = $apiSessions->keys()->toArray();
        Device::where('client_id', $id)
            ->whereNotIn('whatsapp_session', $sessionsToKeep)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Devices synced successfully'
        ]);
    }

    public function whatsAppWebSyncAdminHandle($id)
    {
        if (isDemoMode()) {
            return response()->json([
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
            ]);
        }

        $device = Device::where('whatsapp_session', $id)->first();

        if (!$device) {
            return response()->json([
                'status' => false,
                'message' => 'Device not found'
            ], 404);
        }

        // Call Rapiwa API
        $response = Http::get('https://app.rapiwa.com/api/devices/by-api-key-admin', [
            'api_key' => setting('admin_rapiwa_api'),
            'device_key' => $device->whatsapp_session
        ]);

        $data = $response->json();

        if (!isset($data['sessions']) || empty($data['sessions']['whatsapp_session'])) {
            return response()->json([
                'status' => false,
                'message' => 'No session found'
            ], 404);
        }

        $session = $data['sessions'];

        Device::updateOrCreate(
            [
                'whatsapp_session' => $session['whatsapp_session'], 
                'client_id'        => $device->client_id,           
            ],
            [
                'name'         => $session['name'] ?? null,
                'phone_number' => $session['phone_number'] ?? null,
                'status'       => $session['status'] ?? null,
                'webhook_url'  => $session['webhook_url'] ?? null,

                'jid'                => $session['jid'] ?? null,
                'account_protection' => $session['account_protection'] ?? 0,
                'message_logging'    => $session['message_logging'] ?? 0,
                'read_incoming'      => $session['read_incoming'] ?? 0,
                'connected_at'       => $session['connected_at'] ?? now(),
                'disconnected_at'    => $session['disconnected_at'] ?? null,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Device synced successfully'
        ]);

    }



}
