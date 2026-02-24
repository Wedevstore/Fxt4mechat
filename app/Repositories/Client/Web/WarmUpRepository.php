<?php

namespace App\Repositories\Client\Web;

use App\Models\Device;
use App\Models\WarmupContact;
use App\Models\WhatsAppWarmup;
use App\Traits\RepoResponse;

class WarmUpRepository
{
    use RepoResponse;

    private $model;

    public function __construct(WhatsAppWarmup $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->latest()->withPermission()->paginate(setting('pagination'));
    }

    public function store($request)
    {
        // Store the main WarmUp record
        $warmUp = new $this->model;
        $warmUp->client_id = auth()->user()->client_id;
        $warmUp->device_id = $request->device_id;
        $warmUp->name = $request->name;
        $warmUp->status = $request->status;
        $warmUp->save();

        // Store WarmupContact for each helper device in device_list_id
        if (!empty($request->device_list_id) && is_array($request->device_list_id)) {
            foreach ($request->device_list_id as $deviceId) {
                $device = Device::find($deviceId);
                if ($device) {
                    $warmUpContact = new WarmupContact();
                    $warmUpContact->client_id = auth()->user()->client_id;
                    $warmUpContact->warmup_id = $warmUp->id;
                    $warmUpContact->name = $device->name;
                    $warmUpContact->phone_number = $device->phone_number;
                    $warmUpContact->device_id = $deviceId;
                    $warmUpContact->status = $request->status;
                    $warmUpContact->save();
                }
            }
        }
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    // public function update($request, $id)
    // {
    //     $warmUp             = $this->model->find($id);
    //     $warmUp->client_id  = auth()->user()->client_id;
    //     $warmUp->device_id  = $request->device_id;
    //     $warmUp->name       = $request->name;
    //     $warmUp->status     = $request->status;
    //     $warmUp->save();
    // }

    public function update($request, $id)
    {
        $warmUp = $this->model->findOrFail($id);

        $warmUp->device_id = $request->device_id;
        $warmUp->name      = $request->name;
        $warmUp->status    = $request->status;
        $warmUp->save();

        // Delete old helper devices
        WarmupContact::where('warmup_id', $warmUp->id)->delete();

        // Recreate helper device records
        if (!empty($request->device_list_id) && is_array($request->device_list_id)) {
            foreach ($request->device_list_id as $deviceId) {
                $device = Device::find($deviceId);
                if ($device) {
                    $warmUpContact = new WarmupContact();
                    $warmUpContact->client_id    = auth()->user()->client_id;
                    $warmUpContact->warmup_id    = $warmUp->id;
                    $warmUpContact->name         = $device->name;
                    $warmUpContact->phone_number = $device->phone_number;
                    $warmUpContact->device_id    = $deviceId;
                    $warmUpContact->status       = $request->status;
                    $warmUpContact->save();
                }
            }
        }

        return $warmUp;
    }


    public function destroy($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function statusChange($request)
    {
        $id = $request['id'];

        return $this->model->find($id)->update($request);
    }

    public function warmUpNumberStore($request)
    {
        $warmUpContact               = new WarmupContact();
        $warmUpContact->client_id    = auth()->user()->client_id;
        $warmUpContact->warmup_id    = $request->warmup_id;
        $warmUpContact->name         = $request->name;
        $warmUpContact->phone_number = $request->phone_number;
        $warmUpContact->status       = $request->status;
        $warmUpContact->save();
    }

    public function warmUpDeviceStore($request)
    {
        $device = Device::where('id', $request->device_id)->first();
        $warmUpContact               = new WarmupContact();
        $warmUpContact->client_id    = auth()->user()->client_id;
        $warmUpContact->warmup_id    = $request->warmup_id;
        $warmUpContact->name         = $device->name;
        $warmUpContact->phone_number = $device->phone_number;
        $warmUpContact->device_id    = $request->device_id;
        $warmUpContact->status       = $request->status;
        $warmUpContact->save();
    }
}
