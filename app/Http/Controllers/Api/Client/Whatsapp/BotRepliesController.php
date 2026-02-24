<?php

namespace App\Http\Controllers\Api\Client\Whatsapp;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Whatsapp\BotRepliesResource;
use App\Http\Resources\CannedResponseResource;
use App\Models\BotReply;
use App\Repositories\Client\BotReplyRepository;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BotRepliesController extends Controller
{
    use ApiReturnFormatTrait;

    protected $botReplyRepo;

    public function __construct(BotReplyRepository $botReplyRepo)
    {
        $this->botReplyRepo     = $botReplyRepo;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user      = jwtUser();
            $client_id = $user->client_id;
            $bot_reply = BotReply::where('client_id', $client_id)->withPermission()->latest()->paginate(10);

            $data      = [
                'bot_rplies' => BotRepliesResource::collection($bot_reply),
                'paginate'   => [
                    'total'         => $bot_reply->total(),
                    'current_page'  => $bot_reply->currentPage(),
                    'per_page'      => $bot_reply->perPage(),
                    'last_page'     => $bot_reply->lastPage(),
                    'prev_page_url' => $bot_reply->previousPageUrl(),
                    'next_page_url' => $bot_reply->nextPageUrl(),
                    'path'          => $bot_reply->path(),
                ],
            ];
            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function store(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        $baseRules = [
            'name'       => 'required',
            'reply_type' => 'required',
        ];

        if ($request->input('reply_type') == 'canned_response') {
            $baseRules['reply_text'] = 'required';
        }
        if (($request->input('reply_type') == 'exact_match' || $request->input('reply_type') == 'contains') && $request->input('reply_using_ai') == 0) {
            $baseRules['keywords']   = 'required';
            $baseRules['reply_text'] = 'required';
        }

        $validator = Validator::make($request->all(), $baseRules);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        try {
            $user                 = jwtUser();
            $request['client_id'] = $user->client_id;

            if ($id) {
                $bot_reply = BotReply::findOrFail($id);
                if (! $bot_reply) {
                    return $this->responseWithError('Contact not found.');
                }
                $this->botReplyRepo->update($request, $id);
            } else {
                $this->botReplyRepo->store($request);
            }
            return $this->responseWithSuccess(__('created_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function cannedResponses(): JsonResponse
    {
        try {
            $canned_responses = $this->botReplyRepo->cannedResponses();

            $data = [
                'success' => true,
                'data' => [
                    'canned-responses' => CannedResponseResource::collection($canned_responses),
                ],
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'error' => __('something_went_wrong_please_try_again'),
            ];

            return response()->json($data);
        }
    }

}
