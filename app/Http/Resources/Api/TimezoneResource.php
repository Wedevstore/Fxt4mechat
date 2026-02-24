<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TimezoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                             => (int) $this->id,
            'country_code'                           => $this->country_code ?? '',
            'timezone'                           => $this->timezone ?? '',
            'gmt_offset'                           => $this->gmt_offset ?? '',
            'dst_offset'                           => $this->dst_offset ?? '',
            'raw_offset'                           => $this->raw_offset ?? '',
        ];
    }
}
