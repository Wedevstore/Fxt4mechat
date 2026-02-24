<?php

namespace App\Http\Controllers\Api\Client\Whatsapp;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Whatsapp\SegmentResource;
use App\Models\Segment;
use App\Repositories\Client\SegmentRepository;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SegmentController extends Controller
{
    use ApiReturnFormatTrait;
    protected $segmentRepo;
    public function __construct(SegmentRepository $segmentRepo)
    {
        $this->segmentRepo = $segmentRepo;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user      = jwtUser();
            $client_id = $user->client_id;
            $segment   = Segment::where('client_id', $client_id)->where('status', 1)->latest()->paginate(10);
            $data      = [
                'segment'  => SegmentResource::collection($segment),
                'paginate' => [
                    'total'         => $segment->total(),
                    'current_page'  => $segment->currentPage(),
                    'per_page'      => $segment->perPage(),
                    'last_page'     => $segment->lastPage(),
                    'prev_page_url' => $segment->previousPageUrl(),
                    'next_page_url' => $segment->nextPageUrl(),
                    'path'          => $segment->path(),
                ],
            ];
            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function store(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        try {
            $user                 = jwtUser();
            $request['client_id'] = $user->client_id;
            if ($id) {
                $segment = Segment::findOrFail($id);
                if (! $segment) {
                    return $this->responseWithError(__('contact_not_found'));
                }
                $this->segmentRepo->update($request->all(), $id);
            } else {
                $this->segmentRepo->store($request->all());
            }
            return $this->responseWithSuccess(__('created_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
