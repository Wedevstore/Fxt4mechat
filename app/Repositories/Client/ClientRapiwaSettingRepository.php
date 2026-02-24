<?php
namespace App\Repositories\Client;
use App\Models\Client;
use App\Enums\TypeEnum;
use App\Models\Contact;
use App\Models\Message;
use App\Models\BotGroup;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\Conversation;
use App\Traits\RepoResponse;
use App\Models\ClientSetting;
use App\Services\MetaService;
use App\Traits\TelegramTrait;
use App\Traits\TemplateTrait;
use App\Models\GroupSubscriber;
use Illuminate\Support\Facades\DB;
use App\Models\ClientSettingDetail;
use Illuminate\Support\Facades\Auth;

class ClientRapiwaSettingRepository
{
    use RepoResponse, TelegramTrait, TemplateTrait;

    private $model;

    private $botGroup;

    private $groupSubscriber;

    private $contact;

    private $conversation;

    private $campaign;

    private $message;

    private $client;

    private $template;

    private $metaService;

    private $clientSettingDetail;

    public function __construct(
        ClientSetting $model,
        BotGroup $botGroup,
        GroupSubscriber $groupSubscriber,
        Contact $contact,
        Conversation $conversation,
        Campaign $campaign,
        Message $message,
        Client $client,
        Template $template,
        MetaService $metaService,
        ClientSettingDetail $clientSettingDetail

    ) {
        $this->model            = $model;
        $this->botGroup         = $botGroup;
        $this->groupSubscriber  = $groupSubscriber;
        $this->contact          = $contact;
        $this->conversation     = $conversation;
        $this->campaign         = $campaign;
        $this->message          = $message;
        $this->client           = $client;
        $this->template         = $template;
        $this->metaService      = $metaService;
        $this->clientSettingDetail = $clientSettingDetail;

    }

    public function rapiwaSettingUpdate($request)
    {
        DB::beginTransaction();

        try {
            $client = Auth::user()->client;

            $is_connected   = 1;
            $token_verified = 1;
            $accessToken    = $request->access_token;
            $status         = $request->status;


            $clientSetting = $this->model
                ->where('type', TypeEnum::RAPIWA->value)
                ->where('client_id', $client->id)
                ->first();

            if ($clientSetting) {
                $clientSetting->access_token        = $accessToken;
                $clientSetting->app_id              = null;
                $clientSetting->status              = $status;
                $clientSetting->is_connected        = $is_connected;
                $clientSetting->token_verified      = $token_verified;
                $clientSetting->scopes              = null;
                $clientSetting->granular_scopes     = null;
                $clientSetting->name                = null;
                $clientSetting->data_access_expires_at = null;
                $clientSetting->expires_at          = null;
                $clientSetting->fb_user_id          = null;
                $clientSetting->update();
            } else {
                $clientSetting = $this->model->create([
                    'type'                  => TypeEnum::RAPIWA->value,
                    'client_id'             => $client->id,
                    'access_token'          => $accessToken,
                    'app_id'                => null,
                    'is_connected'          => $is_connected,
                    'token_verified'        => $token_verified,
                    'scopes'                => null,
                    'granular_scopes'       => null,
                    'name'                  => null,
                    'data_access_expires_at'=> null,
                    'expires_at'            => null,
                    'fb_user_id'            => null,
                    'status'                => $status,
                ]);
            }

            DB::commit();
            return $this->formatResponse(true, __('updated_successfully'), route('client.rapiwa.settings'), []);

        } catch (\Throwable $th) {
            DB::rollback();
            if (config('app.debug')) {
                dd($th->getMessage());
            }
            logError('Throwable: ', $th);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), '', []);
        }
    }


    public function removeRapiwaApiKey($request, $id)
    {
        if (isDemoMode()) {
            return $this->formatResponse(false, __('this_function_is_disabled_in_demo_server'), 'client.rapiwa.settings', []);
        }
        DB::beginTransaction();
        try {
            $clientSetting = $this->model->where('type', TypeEnum::RAPIWA)
                ->where('client_id', Auth::user()->client->id)
                ->where('id', $id)
                ->firstOrFail();
            $clientSetting->delete();
            DB::commit();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.rapiwa.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.rapiwa.settings', []);
        }
    }


}
