<?php

namespace App\Http\Resources\Api\Whatsapp;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactListResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id'                      => (int) $this->id,
            'name'                    => $this->name,
            'status'                  => $this->status,
        ];
    }
}