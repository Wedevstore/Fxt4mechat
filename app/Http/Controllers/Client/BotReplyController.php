<?php

namespace App\Http\Controllers\Client;

use App\Models\BotReply;
use App\Traits\RepoResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\DataTables\Client\BotReplyDataTable;
use App\Http\Requests\Client\BotReplyRequest;
use App\Http\Resources\CannedResponseResource;
use App\Repositories\Client\BotReplyRepository;

class BotReplyController extends Controller
{
    use RepoResponse;

    protected $replyRepo;

    public function __construct(BotReplyRepository $replyRepo)
    {
        $this->replyRepo = $replyRepo;
    }

    public function index(BotReplyDataTable $replyDataTable)
    {
        return $replyDataTable->render('backend.client.bot_reply.index');
    }

    public function create()
    {
        return view('backend.client.bot_reply.create');
    }

    public function store(BotReplyRequest $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }
  
        $client = auth()->user()->client;
        // Retrieve the client's active subscription plan
        $activeSubscription = $client->activeSubscription;
        if (!$activeSubscription) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message'   => __('bot_reply_active_subscription'),
            ]);
        } 
        $plan = $activeSubscription->plan;
        // Count the number of bot replies already created
        $existingBotRepliesCount = BotReply::where('client_id', $client->id)->count();
        // Check if the client has reached the maximum number of bot replies allowed
        if ($activeSubscription->max_bot_reply != -1 && $existingBotRepliesCount >= $activeSubscription->max_bot_reply) {
            return response()->json([
                'status'  => false,
                'error'   => __('max_bot_replie_notice'),
            ]);
        }

        DB::beginTransaction();
        try {

            $this->replyRepo->store($request);
            DB::commit();
            Toastr::success(__('create_successful'));
            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('client.bot-reply.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    } 

    public function edit($id)
    {
        $reply = $this->replyRepo->find($id);

        return view('backend.client.bot_reply.edit', compact('reply'));
    }

    public function update(BotReplyRequest $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];
            return response()->json($data);
        }
        
        $client = auth()->user()->client;
        $activeSubscription = $client->activeSubscription;
        if (!$activeSubscription) {
            return response()->json([
                'success' => false,
                'error'   => __('bot_reply_active_subscription'),
            ]);
        }
        $plan = $activeSubscription->plan;
        $reply =  BotReply::findOrFail($id);
        $existingBotRepliesCount =  BotReply::where('client_id', $client->id)
            ->where('id', '!=', $id)
            ->count();
         // Check if the client has reached the maximum number of bot replies allowed
            if ($activeSubscription->max_bot_reply != -1 && $existingBotRepliesCount >= $activeSubscription->max_bot_reply) {
                return response()->json([
                    'status'  => false,
                    'error'   => __('max_bot_replie_notice'),
                ]);
            }

        DB::beginTransaction();
        try {
            $this->replyRepo->update($request, $id);
            DB::commit();
            Toastr::success(__('update_successful'));

            return response()->json([
                'success' => __('update_successful'),
                'route'   => route('client.bot-reply.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function destroy($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->replyRepo->destroy($id);
            Toastr::success(__('delete_successful'));
            $data = [
                'status'  => 'success',
                'message' => __('delete_successful'),
                'title'   => __('success'),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function cannedResponses(): JsonResponse
    {
        try {
            $canned_responses = $this->replyRepo->cannedResponses();
            $data             = [
                'canned_responses' => CannedResponseResource::collection($canned_responses),
                'success'          => true,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'error' => __('something_went_wrong_please_try_again'),
            ];

            return response()->json($data);
        }
    }

    public function statusChange(Request $request): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 400,
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->replyRepo->statusChange($request->all());
            $data = [
                'status'  => 200,
                'message' => __('update_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }            
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'danger',
            ];

            return response()->json($data);
        }
    }
}
