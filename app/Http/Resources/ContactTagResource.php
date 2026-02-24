<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactTagResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'title'  => $this->tag->title,
            'tag_id'  => $this->tag_id,
            'contact_id'  => $this->contact_id ?? null,
            'status' => (bool) ($this->status),
        ];
    }
}
