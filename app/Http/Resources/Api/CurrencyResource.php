<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                             => (int) $this->id,
            'name'                           => $this->name ?? '',
            'symbol'                           => $this->symbol ?? '',
            'code'                           => $this->code ?? '',
            'exchange_rate'                      => $this->exchange_rate ?? '',
            'status'                         => $this->status ?? '',
        ];
    }
}
