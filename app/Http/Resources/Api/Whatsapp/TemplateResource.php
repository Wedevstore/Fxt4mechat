<?php

namespace App\Http\Resources\Api\Whatsapp;

use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                      => (int) $this->id,
            'name'                    => $this->name,
            'template_id'             => $this->template_id,
            'header_media'            => $this->header_media,
            'components'              => $this->components,
            'language'                => $this->language,
            'category'                => $this->category,
            'status'                  => $this->status,
        ];
    }
}
