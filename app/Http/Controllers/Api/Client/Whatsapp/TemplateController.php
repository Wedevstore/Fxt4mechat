<?php

namespace App\Http\Controllers\Api\Client\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Whatsapp\TemplateResource;
use App\Models\Template;
use App\Traits\ApiReturnFormatTrait;

class TemplateController extends Controller
{
    use ApiReturnFormatTrait;

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user      = jwtUser();
            $client_id = $user->client_id;
            $template  = Template::where('client_id', $client_id)->latest()->paginate(10);

            $data      = [
                'template' => TemplateResource::collection($template),
                'paginate' => [
                    'total'         => $template->total(),
                    'current_page'  => $template->currentPage(),
                    'per_page'      => $template->perPage(),
                    'last_page'     => $template->lastPage(),
                    'prev_page_url' => $template->previousPageUrl(),
                    'next_page_url' => $template->nextPageUrl(),
                    'path'          => $template->path(),
                ],
            ];

            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function allTemplate(): \Illuminate\Http\JsonResponse
    {
        try {
            $user      = jwtUser();
            $client_id = $user->client_id;
            $template  = Template::where('client_id', $client_id)->latest()->paginate(50);

            $data = [
                'template'              => TemplateResource::collection($template),
                'paginate' => [
                    'total'             => $template->total(),
                    'current_page'      => $template->currentPage(),
                    'per_page'          => $template->perPage(),
                    'last_page'         => $template->lastPage(),
                    'prev_page_url'     => $template->previousPageUrl(),
                    'next_page_url'     => $template->nextPageUrl(),
                    'path'              => $template->path(),
                ],
            ];

            return $this->responseWithSuccess('template_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
