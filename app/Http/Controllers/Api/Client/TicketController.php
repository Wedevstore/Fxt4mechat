<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TicketRepliesResource;
use App\Http\Resources\Api\TicketResource;
use App\Models\Ticket;
use App\Repositories\TicketRepository;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    use ApiReturnFormatTrait;

    protected $ticketRepo;

    public function __construct(TicketRepository $ticketRepo)
    {

        $this->ticketRepo = $ticketRepo;

    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user   = jwtUser();
            $ticket = Ticket::where('client_staff', $user->id)->with('department')->latest()->paginate(10);

            $data   = [
                'ticket'   => TicketResource::collection($ticket),
                'paginate' => [
                    'total'         => $ticket->total(),
                    'current_page'  => $ticket->currentPage(),
                    'per_page'      => $ticket->perPage(),
                    'last_page'     => $ticket->lastPage(),
                    'prev_page_url' => $ticket->previousPageUrl(),
                    'next_page_url' => $ticket->nextPageUrl(),
                    'path'          => $ticket->path(),
                ],
            ];

            return $this->responseWithSuccess('ticket_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function createTicket(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required',
            'subject'       => 'required',
            'priority'      => 'required',
        ]);

        if ($validator->fails()){
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }

        try {
            $user                    = jwtUser();
            $request['client_staff'] = $user->id;
            $this->ticketRepo->store($request->all());
            return $this->responseWithSuccess('Submitted successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());

        }
    }

    public function replyTicket(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $user                    = jwtUser();
            $request['client_staff'] = $user->id;
            $this->ticketRepo->reply($request->all());

            return $this->responseWithSuccess('Reply successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function replyUpdateTicket(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $user                    = jwtUser();
            $request['client_staff'] = $user->id;
            $this->ticketRepo->replyUpdate($request->all(), $id);
            return $this->responseWithSuccess('Updated successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function replyEdit($id): \Illuminate\Http\JsonResponse
    {
        try {
            $user    = jwtUser();
            $ticket  = $this->ticketRepo->find($id);
            $replies = $ticket->replies;

            $data    = [
                'replies' => TicketRepliesResource::collection($replies),
            ];
            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
