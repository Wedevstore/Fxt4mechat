<?php

namespace App\Http\Controllers\Api\Client\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Telegram\CampaignResource;
use App\Models\Campaign;
use App\Models\Client;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use App\Repositories\Client\TeleCampaignRepository;
use Exception;

class CampaignController extends Controller
{
    use ApiReturnFormatTrait;
    protected $campaign;

    public function __construct(TeleCampaignRepository $campaign)
    {
        $this->campaign     = $campaign;
    }
    public function allcampaign(): \Illuminate\Http\JsonResponse
    {
        try {
            $user     = jwtUser();
            $campaigns = Campaign::where('client_id', $user->client_id)->where('campaign_type', 'telegram')->latest()->paginate(10);
            $client                     = Client::where('id', $user->client_id)->first();
            $activeContactsCount        = $client->contacts()->active()->count();
            $data = [];
            foreach ($campaigns as $campaign) {
                $campaign_contact           = $campaign->total_contact ?? 0;
                $campaign_contact_percent   = ($campaign_contact != 0) ? ($campaign_contact /  $activeContactsCount) * 100 : 0;
                $total_delivered            = $campaign->total_delivered;
                $total_delivered_percent    = ($total_delivered != 0) ? ($total_delivered  / $campaign_contact) * 100 : 0;
                $total_read                 = $campaign->total_read;
                $read_percent               = ($total_read != 0) ? ($total_read  / $campaign_contact) * 100 : 0;
                $data['campaigns'][] = [
                    'campaign_contact'         => $campaign_contact,
                    'campaign_contact_percent' => $campaign_contact_percent,
                    'total_delivered_percent'  => $total_delivered_percent,
                    'total_read'               => $total_read,
                    'campaign'                 => new CampaignResource($campaign),
                ];
            }
            $data['paginate'] = [
                'total'             => $campaigns->total(),
                'current_page'      => $campaigns->currentPage(),
                'per_page'          => $campaigns->perPage(),
                'last_page'         => $campaigns->lastPage(),
                'prev_page_url'     => $campaigns->previousPageUrl(),
                'next_page_url'     => $campaigns->nextPageUrl(),
                'path'              => $campaigns->path(),
            ];
            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }


    public function submitCampaign(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user                 = jwtUser();
            $request['client_id'] = $user->client_id;
            $this->campaign->store($request);
            return $this->responseWithSuccess(__('created_successfully'), 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
