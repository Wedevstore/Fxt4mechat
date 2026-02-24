<?php

namespace App\Http\Resources\Api\Whatsapp;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                => (int) $this->id,
            'campaign_name'                     => $this->campaign_name,
            'campaign_type'                     => $this->campaign_type,
            'total_contact'                     => $this->total_contact ?? 0,
            'total_sent'                        => $this->total_sent ?? 0,
            'total_delivered'                   => $this->total_delivered ?? 0,
            'total_read'                        => $this->total_read ?? 0,
            'total_failed'                      => $this->total_failed ?? 0,
            'schedule_at'                       => $this->schedule_at ?? '',
            'errors'                            => $this->errors ?? '',
            'template_id'                       => $this->template_id ?? '',
            'contact_list_ids'                  => $this->contact_list_ids ?? [],
            'segment_ids'                       => $this->segment_ids ?? [],
            'created_at'                        => $this->created_at,
        ];
    }
}
