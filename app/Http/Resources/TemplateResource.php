<?php

namespace App\Http\Resources;

use App\Models\Template;
use App\Services\TemplateService;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    public function toArray($request)
    {
        $template = Template::find($this->id);
        $data = app(TemplateService::class)->execute($template);
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'category' => $this->category,
            'variables' => $data['variables'],
            'type' => $this->type

        ];
    }
}
