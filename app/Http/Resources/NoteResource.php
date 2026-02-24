<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'contact_id' => $this->contact_id,
            'title'   => $this->title,
            'details' => $this->details,
            'show'    => true,
        ];
    }
}
