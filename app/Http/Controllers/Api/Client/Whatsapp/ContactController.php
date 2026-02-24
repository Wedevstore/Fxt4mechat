<?php

namespace App\Http\Controllers\Api\Client\Whatsapp;
use App\Models\ContactTag;
use App\Models\ContactNote;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TagResource;
use App\Http\Resources\NoteResource;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Client\ContactRepository;
use App\Http\Resources\Api\Whatsapp\ContactResource;

class ContactController extends Controller
{
    use ApiReturnFormatTrait;

    protected $contactsRepo;
    protected $contact;
    public function __construct(
        ContactRepository $contactsRepo,
        Contact $contact
    ) {
        $this->contactsRepo     = $contactsRepo;
        $this->contact     = $contact;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user     = jwtUser();
            $contacts = Contact::where('client_id', $user->client_id)->where('status', 1)->with('list', 'country', 'created_by', 'attributeValue')->latest()->paginate(10);
            $data = [
                'contacts'              => ContactResource::collection($contacts),
                'paginate' => [
                    'total'         => $contacts->total(),
                    'current_page'  => $contacts->currentPage(),
                    'per_page'      => $contacts->perPage(),
                    'last_page'     => $contacts->lastPage(),
                    'prev_page_url' => $contacts->previousPageUrl(),
                    'next_page_url' => $contacts->nextPageUrl(),
                    'path'          => $contacts->path(),
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
            'phone'      => 'required|numeric|min:11|unique:contacts,phone,'.$id,
            'country_id' => 'required|exists:countries,id',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        try {
            $user   = jwtUser();
            $client = $user->client;
            if ($id) {
                $contact = Contact::where('client_id', $client->id)->findOrFail($id);
                if (! $contact) {
                    return $this->responseWithError('Contact not found.');
                }
                $this->contactsRepo->update($request, $id);
            } else {
                $this->contactsRepo->store($request);
            }
            return $this->responseWithSuccess(__('created_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function contactDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        $user = jwtUser();
        $client = $user->client;
        try {
            $user = jwtUser();
            $client = $user->client;
            $contact = $this->contact->where('client_id', $client->id)->find($request->contact_id);
            $notes   = ContactNote::where('contact_id', $request->contact_id)->latest()->get();
            $tags    = ContactTag::where('contact_id', $request->contact_id)->orderBy('id', 'DESC')->get();
            $data    = [
                'contact' => [
                    'id'                   => $contact->id,
                    'name'                 => $contact->name,
                    'phone'                => (isDemoMode()) ? '*********' : $contact->phone,
                    'avatar'               => $contact->avatar ?? '',
                    'country_id'           => $contact->country_id ?? '',
                    'client_id'            => $contact->client_id,
                    'group_chat_id'        => $contact->group_chat_id ?? '',
                    'group_id'             => $contact->group_id ?? '',
                    'type'                 => $contact->type ?? 'whatsapp',
                    'bot_reply'            => $contact->bot_reply ?? 1,
                    'is_blacklist'         => $contact->is_blacklist ?? 0,
                    'email'                => $contact->email ?? '',
                    'address'              => $contact->address ?? '',
                    'city'                 => $contact->city ?? '',
                    'state'                => $contact->state ?? '',
                    'zipcode'              => $contact->zipcode ?? '',
                    'birthdate'            => $contact->birthdate ?? '',
                    'gender'               => $contact->gender ?? '',
                    'occupation'           => $contact->occupation ?? '',
                    'company'              => $contact->company ?? '',
                    'rating'               => $contact->rating ?? '',
                    'last_conversation_at' => Carbon::parse($contact->last_conversation_at)->format('d/m/Y'),
                    'assignee_id'          => nullCheck($contact->assignee_id),
                    'conversation_id'      => nullCheck(@$contact->lastConversation->unique_id),
                ],
                'notes'   => NoteResource::collection($notes),
                'tags'    => TagResource::collection($tags),
            ];

            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data, 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 200);
        }
    }

    public function block($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
            $response = $this->contactsRepo->addBlock($id);

            $data     = [
                'status'  => 'success',
                'message' => __($response['message']),
                'title'   => __('success'),
            ];

            return $this->responseWithSuccess('chat_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
