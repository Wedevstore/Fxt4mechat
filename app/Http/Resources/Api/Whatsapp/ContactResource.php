<?php

namespace App\Http\Resources\Api\Whatsapp;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{


    public function toArray($request): array
    {
        // Base attributes for the Contact model
        $data = [
            'id'                      => (int) $this->id,
            'name'                    => $this->name,
            'avatar'                  => getFileLink('80x80', $this->avatar),
            'phone'                   => $this->phone,
            'email'                   => $this->email ?? '',
            'address'                 => $this->address ?? '',
            'city'                    => $this->city ?? '',
            'state'                   => $this->state ?? '',
            'zipcode'                 => $this->zipcode ?? '',
            'birthdate'               => $this->birthdate ?? '',
            'gender'                  => $this->gender ?? '',
            'occupation'              => $this->occupation ?? '',
            'company'                 => $this->company ?? '',
            'source'                  => $this->source ?? '',
            'type'                    => $this->type ?? '',
            'status'                  => $this->status ?? 1,
            'is_blacklist'            => $this->is_blacklist ?? 0,
            'last_conversation_at'    => $this->last_conversation_at ?? '',
            'has_conversation'        => $this->has_conversation ?? '',
            'has_unread_conversation' => $this->has_unread_conversation ?? '',
            'country_id'              => $this->country_id ?? '',
            'contact_list_id'         => $this->whenLoaded('list', fn() => $this->list ? $this->list->name : ''),
            'country'                 => $this->whenLoaded('country', fn() => $this->country ? $this->country->name : ''),
        ];
        if ($this->relationLoaded('attributeValue')) {
            foreach ($this->attributeValue as $attribute) {
                $data[$attribute->attribute->title] = $attribute->attr_value;
            }
        }
    
        return $data;
    }
    
}
