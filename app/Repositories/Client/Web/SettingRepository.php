<?php

namespace App\Repositories\Client\Web;

use App\Enums\TypeEnum;
use App\Models\Client;
use App\Models\ClientSetting;
use App\Models\ClientSettingDetail;
use App\Models\Contact;
use App\Models\Device;
use App\Traits\RepoResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingRepository
{
    use RepoResponse;

    private $model;

    private $contact;

    private $client;

    private $clientSettingDetail;

    public function __construct(
        ClientSetting $model,
        Contact $contact,
        Client $client,
        ClientSettingDetail $clientSettingDetail
    )
    {
        $this->model            = $model;
        $this->contact          = $contact;
        $this->client           = $client;
        $this->clientSettingDetail = $clientSettingDetail;
    }

    public function webSettingUpdate($request)
    {
        DB::beginTransaction();

        try {
            $client = Auth::user()->client;
            $is_connected   = 1;
            $token_verified = 1;
            $scopes         = null;
            $token          = $request->client_key;
            $name           = $request->url;
            $status         = 1;

            $clientSetting = $this->model
                ->where('type', TypeEnum::RAPIWA->value)
                ->where('client_id', $client->id)
                ->first();

            if ($clientSetting) {
                $clientSetting->access_token        = $token;
                $clientSetting->app_id              = $name;
                $clientSetting->status              = $status;
                $clientSetting->is_connected        = $is_connected;
                $clientSetting->token_verified      = $token_verified;
                $clientSetting->scopes              = $scopes;
                $clientSetting->granular_scopes     = null; 
                $clientSetting->name                = null;
                $clientSetting->data_access_expires_at = null;
                $clientSetting->expires_at          = null;
                $clientSetting->fb_user_id          = null;
                $clientSetting->update();
            } else {
                $clientSetting = $this->model->create([
                    'type'                  => TypeEnum::RAPIWA,
                    'client_id'             => $client->id,
                    'access_token'          => $token,
                    'app_id'                => $name,
                    'is_connected'          => $is_connected,
                    'token_verified'        => $token_verified,
                    'scopes'                => $scopes,
                    'granular_scopes'       => null, 
                    'name'                  => null,
                    'data_access_expires_at'=> null,
                    'expires_at'            => null,
                    'fb_user_id'            => null,
                    'status'                => 1,
                ]);
            }

            DB::commit();

            return $this->formatResponse(
                true,
                __('updated_successfully'),
                route('client.web.setting.update')
            );

        } catch (\Throwable $th) {
            DB::rollback();
            if (config('app.debug')) {
                dd($th->getMessage());
            }
            logError('Throwable: ', $th);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), '', []);
        }
    }

    public function removeWhatsAppWebToken($request, $id)
    {
        if (isDemoMode()) {
            return $this->formatResponse(false, __('this_function_is_disabled_in_demo_server'), 'client.web.setting', []);
        }
        DB::beginTransaction();
        try {
            $clientSetting = $this->model->where('type', TypeEnum::RAPIWA)
                ->where('client_id', Auth::user()->client->id)
                ->where('id', $id)
                ->firstOrFail();
            $clientSetting->delete();
            Device::where('client_id', Auth::user()->client->id)->delete();

            DB::commit();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.web.setting', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.web.setting', []);
        }
    }


}

?>