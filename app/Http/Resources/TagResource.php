<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'contact_id' => $this->contact_id,
            'title'  => $this->title ?? $this->tag->title,
            'status' => (bool) ($this->status),
        ];
    }
}
