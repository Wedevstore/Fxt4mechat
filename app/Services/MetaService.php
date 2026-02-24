<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class MetaService
{

    private $FACEBOOK_BASE_URL = 'https://graph.facebook.com';
    private $API_VERSION = 'v20.0';
    public function __construct()
    {
        $this->FACEBOOK_BASE_URL = env('FACEBOOK_BASE_URL', 'https://graph.facebook.com');
        $this->API_VERSION = env('META_API_VERSION', 'v20.0');
    }
    public function execute($request)
    {
    }

    public function getPhoneNumbers($wabaId, $clientSetting)
    {
        $fields = 'display_phone_number,certificate,name_status,new_certificate,new_name_status,verified_name,quality_rating,messaging_limit_tier';
        $response = $this->makeFacebookApiRequest("/{$wabaId}/phone_numbers", 'GET', [
            'fields' => $fields,
        ], $clientSetting->access_token);
        return $response;
    }

    public function getPhoneNumberStatus($phoneNumberId,$clientSetting)
    {
        try {
            $response = $this->makeFacebookApiRequest("/{$phoneNumberId}", 'GET', [
                'fields' => 'status, code_verification_status , quality_score, health_status',
            ], $clientSetting->access_token);
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $response;
    }

    public function getBusinessProfileDetails($phoneNumberId, $clientSetting)
    {
        $url = "/{$phoneNumberId}/whatsapp_business_profile?fields=about,address,description,email,profile_picture_url,websites,vertical";
        $response = $this->makeFacebookApiRequest($url, 'GET', null, $clientSetting->access_token);
        return $response;
    }


    public function updateBusinessProfile($phoneNumberId, $accessToken, $request)
    {
        $url = "/{$phoneNumberId}/whatsapp_business_profile";
        $data = [
            'messaging_product' => 'whatsapp',
            'address'           => $request->address,
            'description'       => $request->description,
            'vertical'          => $request->vertical,
            'about'             => $request->about,
            'email'             => $request->email,
            'websites'          => $request->websites ?? [],
            // 'profile_picture_handle' => $request->profile_picture_handle ?? null
        ];
        $jsonData = json_encode($data);
        $response = $this->makeFacebookApiRequest($url, 'POST', $jsonData, $accessToken);
        return $response;
    }
  
    
    public function getBusinessAccount($wabaId, $clientSetting)
    {
        $url = "/{$wabaId}?fields=id,name,timezone_id";
        $response = $this->makeFacebookApiRequest($url, 'GET', null, $clientSetting->access_token);
        return $response;
    }

    public function getAccountReviewStatus($wabaId, $clientSetting)
    {
        $fields = 'account_review_status';
        $response = $this->makeFacebookApiRequest("/{$wabaId}", 'GET', [
            'fields' => $fields,
        ], $clientSetting->access_token);
        return $response;
    }

    public function subscribeApps($wabaId,$clientSetting)
    {
        $result = $this->makeFacebookApiRequest("/{$wabaId}/subscribed_apps", 'POST',null, $clientSetting->access_token);
        return $result;
    } 

    public function unSubscribeApps($wabaId,$clientSetting)
    {
        $result = $this->makeFacebookApiRequest("/{$wabaId}/subscribed_apps", 'DELETE',null, $clientSetting->access_token);
        return $result;
    } 

    public function subscribeToMessages($wabaId, $clientSetting)
        {
            $data = json_encode(['subscribed_fields' => ['messages']]);
            $result = $this->makeFacebookApiRequest("/{$wabaId}/subscribed_apps", 'POST', $data, $clientSetting->access_token);
            return $result;
        }


    public function overrideCallbackURL($wabaId, $callbackUri, $verifyToken,$clientSetting)
    {
        $data = json_encode(['override_callback_uri' => $callbackUri, 'verify_token' => $verifyToken]);
        $result = $this->makeFacebookApiRequest("/{$wabaId}/subscribed_apps", 'POST', $data, $clientSetting->access_token);
        return $result;
    }

    public function overridePhoneCallbackUrl($phoneNumberId, $callbackUri, $verifyToken,$clientSetting)
    {
        $data = json_encode(['override_callback_uri' => $callbackUri, 'verify_token' => $verifyToken]);
        $result = $this->makeFacebookApiRequest("/{$phoneNumberId}", 'POST', $data, $clientSetting->access_token);
        return $result;
    }

    public function getAllSubscriptionsForWABA($wabaId, $accessToken)
    {
        $url = $this->FACEBOOK_BASE_URL . '/' . $this->API_VERSION . '/' . $wabaId . '/subscribed_apps';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        try {
            $response = curlRequest($url, null, 'GET', $headers);
            if (isset($response->error)) {
                throw new \Exception($response->error->message ?? 'An error occurred');
            }
            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (\Exception $e) {
            logError('getAllSubscriptionsForWABA: ', $e);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getAllBusinessAccounts($wabaId, $accessToken)
    {
        $url = $this->FACEBOOK_BASE_URL . '/' . $this->API_VERSION . '/' . $wabaId . '/owned_whatsapp_business_accounts';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        try {
            $response = curlRequest($url, null, 'GET', $headers);
            if (isset($response->error)) {
                throw new \Exception($response->error->message ?? 'An error occurred');
            }
            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (\Exception $e) {

            logError('get All Business Accounts : ', $e);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function registerNumber($phoneNumberId, $clientSetting)
    {
        $response = $this->makeFacebookApiRequest("/{$phoneNumberId}/register", 'POST', [
            'messaging_product' => "whatsapp",
            'pin' => "123456",
        ], $clientSetting->access_token);
        return $response;
    }

    function deRegister($phoneNumberId, $clientSetting)
    {
        $result = $this->makeFacebookApiRequest("/{$phoneNumberId}/deregister", 'POST',null , $clientSetting->access_token);
        return $result;
    }


    public function makeFacebookApiRequest($endpoint, $method = 'GET', $data = [], $accessToken = null)
    {
        $url = "{$this->FACEBOOK_BASE_URL}/{$this->API_VERSION}{$endpoint}";
        $headers = [
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ];
        try {

            $response = curlRequest($url, $data, $method, $headers);

            Log::info('makeFacebookApiRequest : ', [$response]);
            
            if (isset($response->error)) {
                return ['success' => false, 'message' => $response->error->message];
            }
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            logError('makeFacebookApiRequest: ', $e);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function getAssignedUser($wabaId, $clientSetting)
    {
        logInfo('$wabaId', $wabaId);

        $result = $this->makeFacebookApiRequest("/{$wabaId}/assigned_users", 'GET', [
            'business' => $wabaId,
        ], $clientSetting->access_token);

        return $result;
    } 

    public function assignUserToWABA($wabaId, $clientSetting)
    {
        $result = $this->makeFacebookApiRequest("/{$wabaId}/assigned_users", 'POST', [
            'user' => "",
            'tasks' => [],
        ], $clientSetting->access_token);

        return $result;
    } 






    
}
