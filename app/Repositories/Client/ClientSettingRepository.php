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
use Telegram\Bot\Laravel\Facades\Telegram;

class ClientSettingRepository
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

    public function whatsAppSettingUpdate($request)
    {
        DB::beginTransaction();
        try {
            $client = Auth::user()->client;
            $is_connected = 0;
            $token_verified = 0;
            $scopes         = null;
            $accessToken  = $request->access_token;
            $url          = 'https://graph.facebook.com/debug_token?input_token=' . $accessToken . '&access_token=' . $accessToken;
            $ch           = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response     = curl_exec($ch);
            $responseData = json_decode($response, true);
            if (isset($responseData['error'])) {
                return $this->formatResponse(false, $responseData['error']['message'], 'client.whatsapp.settings', []);
            } else {
                if (isset($responseData['data']['is_valid']) && $responseData['data']['is_valid'] === true) {
                    $is_connected = 1;
                    $token_verified = 1;
                    $scopes = $responseData['data']['scopes'];
                } else {
                    return $this->formatResponse(false, __('access_token_is_not_valid'), 'client.whatsapp.settings', []);
                }
            }
            $scopes = $responseData['data']['scopes'];
            $dataAccessExpiresAt = isset($responseData['data']['data_access_expires_at']) ?
                (new \DateTime())->setTimestamp($responseData['data']['data_access_expires_at']) : null;
            $dataExpiresAt = isset($responseData['data']['expires_at']) ?
                (new \DateTime())->setTimestamp($responseData['data']['expires_at']) : null;
            curl_close($ch); 

            $clientSetting       = $this->model
                ->where('type', TypeEnum::WHATSAPP->value)
                ->where('client_id', Auth::user()->client->id)
                ->first();
            if ($clientSetting) {   
                $clientSetting                      = $this->model->where('type', TypeEnum::WHATSAPP)->where('client_id', Auth::user()->client->id)->first();
                $clientSetting->access_token        = $accessToken;
                $clientSetting->phone_number_id     = $request->phone_number_id;
                $clientSetting->business_account_id = $request->business_account_id;
                $clientSetting->app_id              = $responseData['data']['app_id'] ?? $request->app_id;
                $clientSetting->is_connected        = $is_connected;
                $clientSetting->token_verified      = $token_verified;
                $clientSetting->scopes              = $scopes;
                $clientSetting->granular_scopes     = $responseData['data']['granular_scopes'] ?? null;
                $clientSetting->name                = $responseData['data']['application'] ?? null;
                $clientSetting->data_access_expires_at = $dataAccessExpiresAt;
                $clientSetting->expires_at          = $dataExpiresAt;
                $clientSetting->fb_user_id          = $responseData['data']['user_id'] ?? null;
                $clientSetting->update();
            } else {
                $clientSetting = $this->model->create([
                    'type'                => TypeEnum::WHATSAPP,
                    'client_id'           => Auth::user()->client->id,
                    'access_token'        => $accessToken,
                    'phone_number_id'     => $request->phone_number_id,
                    'business_account_id' => $request->business_account_id,
                    'app_id'              => $responseData['data']['app_id'] ?? $request->app_id,
                    'is_connected'        => $is_connected,
                    'token_verified'      => $token_verified,
                    'scopes'              => $scopes,
                    'granular_scopes'     => $responseData['data']['granular_scopes'] ?? null,
                    'name'                => $responseData['data']['application'] ?? null,
                    'data_access_expires_at' => $dataAccessExpiresAt,
                    'expires_at'          => $dataExpiresAt,
                    'fb_user_id'          => $responseData['data']['user_id'] ?? null,
                ]);
            }
            $this->createPhoneAndBusinessProfile($clientSetting->business_account_id, $clientSetting);
             $this->getLoadTemplate($clientSetting);

            DB::commit();
            return $this->formatResponse(true, __('updated_successfully'), route('client.whatsapp.settings'), []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), '', []);
        }
    }



    private function createPhoneAndBusinessProfile($businessAccountId, $clientSetting)
    {
        try {
            $phoneNumbers = $this->metaService->getPhoneNumbers($businessAccountId, $clientSetting);
            if ($phoneNumbers['success'] == true && isset($phoneNumbers['data'])) {
                $results = $phoneNumbers['data']->data ?? [];
                foreach ($results as $result) {
                    $businessProfile = $this->metaService->getBusinessProfileDetails($result->id ?? null, $clientSetting);
                    $accountReviewStatus = $this->metaService->getAccountReviewStatus($businessAccountId ?? null, $clientSetting);
                    $phoneNumberStatus = $this->metaService->getPhoneNumberStatus($result->id ?? null, $clientSetting);
                    $businessAccount = $this->metaService->getBusinessAccount($businessAccountId ?? null, $clientSetting);
                    $this->clientSettingDetail->updateOrCreate(
                        [
                            'client_setting_id' => $clientSetting->id,
                            'phone_number_id' => $result->id ?? null
                        ],
                        [
                            'verified_name' => $result->verified_name ?? null,
                            'display_phone_number' => $result->display_phone_number ?? null,
                            'phone_number_id' => $result->id ?? null,
                            'quality_rating' => $result->quality_rating ?? null,
                            'account_review_status' => $accountReviewStatus['data'] ?? null,
                            'number_status' => $phoneNumberStatus->data->status ?? null,
                            'code_verification_status' => $phoneNumberStatus->data->code_verification_status ?? $result->code_verification_status ?? null,
                            'certificate' => $result->certificate ?? null,
                            'new_certificate' => $result->new_certificate ?? null,
                            'messaging_limit_tier' => $result->messaging_limit_tier ?? null,
                            'profile_info' => [
                                'business_account_name' => $businessAccount['data']->name ?? null,
                                'webhook_configuration' => $result->webhook_configuration->application ?? null,
                                'message_template_namespace' => $accountReviewStatus['data']->message_template_namespace ?? null,
                                'address' => $businessProfile['data']->data[0]->address ?? null,
                                'email' => $businessProfile['data']->data[0]->email ?? null,
                                'description' => $businessProfile['data']->data[0]->description ?? null,
                                'vertical' => $businessProfile['data']->data[0]->vertical ?? null,
                                'about' => $businessProfile['data']->data[0]->about ?? null,
                                'websites' => json_encode($businessProfile['data']->data[0]->websites ?? []),
                                'profile_picture_url' => $businessProfile['data']->data[0]->profile_picture_url ?? null,
                            ]
                        ]
                    );
                }
                if (isset($businessAccount['data'])) {
                    $clientSetting->business_account_name = $businessAccount['data']->name ?? $clientSetting->business_account_name;
                    $clientSetting->update();
                }
            }
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    
    

    public function whatsAppsync($request)
    {
        DB::beginTransaction();
        try {
            $clientSetting = @Auth::user()->client->whatsappSetting;
            if (!$clientSetting) {
                return $this->formatResponse(false, __('whatsapp_setting_not_found'), 'client.whatsapp.settings', []);
            }
            $accessToken = $clientSetting->access_token;
            $url = 'https://graph.facebook.com/debug_token?input_token=' . $accessToken . '&access_token=' . $accessToken;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            if (isset($responseData['error'])) {
                return $this->formatResponse(false, $responseData['error']['message'], 'client.whatsapp.settings', []);
            }
            if (isset($responseData['data']['is_valid']) && $responseData['data']['is_valid'] === true) {
                $clientSetting->is_connected = 1;
                $clientSetting->token_verified = 1;
                $clientSetting->scopes = $responseData['data']['scopes'];
                $clientSetting->granular_scopes = $responseData['data']['granular_scopes'] ?? null;
                // $clientSetting->application_name = $responseData['data']['application'] ?? null;
                $clientSetting->data_access_expires_at = isset($responseData['data']['data_access_expires_at']) ?
                    (new \DateTime())->setTimestamp($responseData['data']['data_access_expires_at']) : null;
                $clientSetting->expires_at = isset($responseData['data']['expires_at']) ?
                    (new \DateTime())->setTimestamp($responseData['data']['expires_at']) : null;
                $clientSetting->fb_user_id = $responseData['data']['user_id'] ?? null;
                $clientSetting->name    = $responseData['data']['application'] ?? null;
                $clientSetting->save();
                  // $this->createPhoneAndBusinessProfile($this->business_account_id, $clientSetting);
                  $phoneNumbers = $this->metaService->getPhoneNumbers($clientSetting->business_account_id, $clientSetting);
                  $phoneNumberStatus = $this->metaService->getPhoneNumberStatus($clientSetting->phone_number_id ?? null, $clientSetting);
                  $accountReviewStatus = $this->metaService->getAccountReviewStatus($clientSetting->business_account_id ?? null, $clientSetting);
                  $businessProfile = $this->metaService->getBusinessProfileDetails($clientSetting->phone_number_id ?? null, $clientSetting);
                //   $businessAccount = $this->metaService->getBusinessAccount($clientSetting->business_account_id ?? null, $clientSetting);
                  $this->clientSettingDetail->updateOrCreate(
                      [
                          'client_setting_id' => $clientSetting->id,
                          'phone_number_id' => $clientSetting->phone_number_id ?? null
                      ],
                      [
                          'verified_name' => $phoneNumbers['data']->data[0]->verified_name ?? null,
                          'display_phone_number' => $phoneNumbers['data']->data[0]->display_phone_number ?? null,
                          'phone_number_id' => $clientSetting->phone_number_id ?? null,
                          'quality_rating' => $phoneNumbers['data']->data[0]->quality_rating ?? null,
                          'account_review_status' => $accountReviewStatus['data'] ?? null,
                          'number_status' => $phoneNumberStatus->data->status ?? null,
                          'code_verification_status' => $phoneNumberStatus->data->code_verification_status ?? $phoneNumbers['data']->data[0]->code_verification_status ?? null,
                          'certificate' => $phoneNumbers['data']->data[0]->certificate ?? null,
                          'new_certificate' => $phoneNumbers['data']->data[0]->new_certificate ?? null,
                          'messaging_limit_tier' => $phoneNumbers['data']->data[0]->messaging_limit_tier ?? null,
                          'profile_info' => [
                              // 'business_account_name' => $businessAccount['data']->name ?? null,
                              'webhook_configuration' => $phoneNumbers['data']->data[0]->webhook_configuration->application ?? null,
                              'message_template_namespace' => $accountReviewStatus['data']->message_template_namespace ?? null,
                              'address' => $businessProfile['data']->data[0]->address ?? null,
                              'email' => $businessProfile['data']->data[0]->email ?? null,
                              'description' => $businessProfile['data']->data[0]->description ?? null,
                              'vertical' => $businessProfile['data']->data[0]->vertical ?? null,
                              'about' => $businessProfile['data']->data[0]->about ?? null,
                              'websites' => json_encode($businessProfile['data']->data[0]->websites ?? []),
                              'profile_picture_url' => $businessProfile['data']->data[0]->profile_picture_url ?? null,
                          ]
                      ]
                  );
                $this->syncTemplate($clientSetting);
                DB::commit();
                return $this->formatResponse(true, __('whatsapp_settings_synced_successfully'), 'client.whatsapp.settings', []);
            } else {
                return $this->formatResponse(false, __('access_token_is_not_valid'), 'client.whatsapp.settings', []);
            }
        } catch (\Throwable $e) {
            DB::rollback();
            logError('Throwable: ', $e);
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            return $this->formatResponse(false, $e->getMessage(), 'client.whatsapp.settings', []);
        }
    }


    public function removeWhatsAppToken($request, $id)
    {
        if (isDemoMode()) {
            return $this->formatResponse(false, __('this_function_is_disabled_in_demo_server'), 'client.whatsapp.settings', []);
        }
        DB::beginTransaction();
        try {
            $clientSetting = $this->model->where('type', TypeEnum::WHATSAPP)
                ->where('client_id', Auth::user()->client->id)
                ->where('id', $id)
                ->firstOrFail();
            $clientSetting->delete();
            $this->template->where('client_id', Auth::user()->client->id)->where('client_setting_id',$clientSetting->id)->delete();
            // $this->client->where('id', Auth::user()->client->id)->update([
            //     'webhook_verify_token' => Str::random(40)
            // ]);
            DB::commit();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.whatsapp.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.whatsapp.settings', []);
        }
    }

    public function telegramUpdate($request)
    {
        DB::beginTransaction();
        try {
            $result           = [];
            $is_connected     = 0;
            $webhook_verified = 0;
            $token_verified   = 0;
            $accessToken      = $request->access_token;
            config(['telegram.bots.mybot.token' => $accessToken]);
            $result           = Telegram::getMe();
            if (!isset($result) || $result['is_bot'] !== true) {
                return $this->formatResponse(false, __('bot_token_is_not_valid'), 'client.telegram.settings', []);
            }
            $is_connected = 1;
            $token_verified = 1;
            $scopes = [];
            if ($result['can_join_groups']) {
                $scopes[] = 'can_join_groups';
            }
            if (!empty($result['can_read_all_group_messages'])) {
                $scopes[] = 'can_read_all_group_messages';
            }
            if (!empty($result['supports_inline_queries'])) {
                $scopes[] = 'supports_inline_queries';
            }
            $webhookResponse = $this->setWebhook($accessToken);
            $webhook_verified = !empty($webhookResponse) && $webhookResponse === true ? 1 : 0;

            $clientSetting    = $this->model
                ->where('type', TypeEnum::TELEGRAM->value)
                ->where('client_id', Auth::user()->client->id)
                ->first();
            if ($clientSetting) {
                $clientSetting                              = $this->model->where('type', TypeEnum::TELEGRAM)->where('client_id', Auth::user()->client->id)->first();
                $clientSetting->access_token                = $accessToken;
                $clientSetting->is_connected                = $is_connected;
                $clientSetting->webhook_verified            = $webhook_verified;
                $clientSetting->bot_id                      = $result['id'];
                $clientSetting->name                        = $result['first_name'];
                $clientSetting->username                    = $result['username'];
                $clientSetting->token_verified              = $token_verified;
                $clientSetting->scopes                      = $scopes ?? [];
                $clientSetting->granular_scopes             = $scopes ?? [];
                $clientSetting->update();
            } else {
                $clientSetting = $this->model->create([
                    'type'                        => TypeEnum::TELEGRAM,
                    'client_id'                   => Auth::user()->client->id,
                    'bot_id'                      => $result['id'],
                    'name'                        => $result['first_name'],
                    'username'                    => $result['username'],
                    'access_token'                => $accessToken,
                    'phone_number_id'             => $request->phone_number_id,
                    'business_account_id'         => $request->business_account_id,
                    'is_connected'                => $is_connected,
                    'webhook_verified'            => $webhook_verified,
                    'token_verified'              => $token_verified,
                    'scopes'                      => $scopes,
                    'granular_scopes'             => $scopes,
                ]);
            }
            DB::commit();
            return $this->formatResponse(true, __('updated_successfully'), 'client.telegram.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.telegram.settings', []);
        }
    }

    public function removeTelegramToken($request, $id)
    {
        if (isDemoMode()) {
            return $this->formatResponse(false, __('this_function_is_disabled_in_demo_server'), 'client.telegram.settings', []);
        }
        DB::beginTransaction();
        try {
            $clientSetting = $this->model->where('type', TypeEnum::TELEGRAM)
                ->where('client_id', Auth::user()->client->id)
                ->where('id', $id)
                ->firstOrFail();
            $botgroups     = $this->botGroup->where('client_setting_id', $clientSetting->id)->get();
            foreach ($botgroups as $botgroup) {
                $this->groupSubscriber->where('group_id', $botgroup->id)->delete();
                $this->contact->where('group_id', $botgroup->id)->delete();
                $messages = $this->message->whereHas('contact', function ($query) use ($botgroup) {
                    $query->where('group_id', $botgroup->id);
                })->get();
                foreach ($messages as $message) {
                    $message->delete();
                }
                $botgroup->delete();
            }
            $clientSetting->delete();
            DB::commit();

            return $this->formatResponse(true, __('deleted_successfully'), 'client.telegram.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.telegram.settings', []);
        }
    }

    public function telegramSettingsync($id)
    {
        DB::beginTransaction();
        try {
            $result           = [];
            $is_connected     = 0;
            $webhook_verified = 0;
            $token_verified   = 0;
            $accessToken      = @Auth::user()->client->telegramSetting->access_token;
            config(['telegram.bots.mybot.token' => $accessToken]);
            $result           = Telegram::getMe();
            if (!isset($result) || $result['is_bot'] !== true) {
                return $this->formatResponse(false, __('bot_token_is_not_valid'), 'client.telegram.settings', []);
            }
            $is_connected = 1;
            $token_verified = 1;
            $scopes = [];
            if ($result['can_join_groups']) {
                $scopes[] = 'can_join_groups';
            }
            if (!empty($result['can_read_all_group_messages'])) {
                $scopes[] = 'can_read_all_group_messages';
            }
            if (!empty($result['supports_inline_queries'])) {
                $scopes[] = 'supports_inline_queries';
            }
            $webhookResponse = $this->setWebhook($accessToken);
            $webhook_verified = !empty($webhookResponse) && $webhookResponse === true ? 1 : 0;
            $clientSetting    = $this->model
                ->where('type', TypeEnum::TELEGRAM->value)
                ->where('client_id', Auth::user()->client->id)
                ->findOrFail($id);
            $clientSetting->access_token                = $accessToken;
            $clientSetting->is_connected                = $is_connected;
            $clientSetting->webhook_verified            = $webhook_verified;
            $clientSetting->bot_id                      = $result['id'];
            $clientSetting->name                        = $result['first_name'];
            $clientSetting->username                    = $result['username'];
            $clientSetting->token_verified              = $token_verified;
            $clientSetting->scopes                      = $scopes ?? [];
            $clientSetting->granular_scopes             = $scopes ?? [];
            $clientSetting->update();
            DB::commit();
            return $this->formatResponse(true, __('updated_successfully'), 'client.telegram.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.telegram.settings', []);
        }
    }


    public function billingDetailsUpdate($request, $id)
    {
        $client                   = Client::findOrFail($id);
        $client->billing_name     = $request->billing_name;
        $client->billing_email    = $request->billing_email;
        $client->billing_address  = $request->billing_address;
        $client->billing_city     = $request->billing_city;
        $client->billing_state    = $request->billing_state;
        $client->billing_zip_code = $request->billing_zipcode;
        $client->billing_country  = $request->billing_country;
        $client->billing_phone    = $request->billing_phone;
        $client->save();
    }

    public function aiCredentialUpdate($request)
    {
        DB::beginTransaction();
        try {
            $this->client->where('id', Auth::user()->client->id)->update(
                [
                    'open_ai_key' => $request->ai_secret_key,
                ]
            );
            DB::commit();
            return $this->formatResponse(true, __('updated_successfully'), 'ai_writer.setting', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'ai_writer.setting', []);
        }
    }





}
