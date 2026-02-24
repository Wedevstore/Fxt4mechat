<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                             => (int) $this->id,
            'name'                           => $this->name ?? '',
            'iso3'                           => $this->iso3 ?? '',
            'iso2'                           => $this->iso2 ?? '',
            'phonecode'                      => $this->phonecode ?? '',
            'currency'                       => $this->currency ?? '',
            'currency_symbol'                => $this->currency_symbol ?? '',
            'status'                         => $this->status ?? '',
        ];
    }
}
