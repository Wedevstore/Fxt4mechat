<?php

namespace App\Http\Controllers\Api\Setting;
use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Http\Resources\Api\CountryResource;

class CountryController extends Controller
{
    use ApiReturnFormatTrait;

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user     = jwtUser();
            $ticket   = Country::latest()->paginate(10);
            $data = [
                'ticket'                => CountryResource::collection($ticket),
                'paginate' => [
                    'total'             => $ticket->total(),
                    'current_page'      => $ticket->currentPage(),
                    'per_page'          => $ticket->perPage(),
                    'last_page'         => $ticket->lastPage(),
                    'prev_page_url'     => $ticket->previousPageUrl(),
                    'next_page_url'     => $ticket->nextPageUrl(),
                    'path'              => $ticket->path(),
                ],
            ];

            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

}
