<?php

namespace App\Http\Resources;

use App\Models\WebTemplate;
use App\Services\TemplateService;
use Illuminate\Http\Resources\Json\JsonResource;

class WebTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'status'     => $this->status,
        ];
    }
}
