<?php

namespace App\Http\Controllers\Client;

use App\DataTables\Client\ContactsDataTable;
use App\DataTables\Client\TelegramSubscriberDataTable;
use App\Enums\TypeEnum;
use App\Exports\FilteredContact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ContactsRequest;
use App\Http\Requests\Client\ContactUpdateRequest;
use App\Http\Resources\ContactResource;
use App\Models\BotGroup;
use App\Models\Contact;
use App\Models\ContactRelationSegments;
use App\Models\ContactsList;
use App\Models\Country;
use App\Models\Segment;
use App\Repositories\Client\ContactAttributeRepository;
use App\Repositories\Client\ContactListRepository;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\SegmentRepository;
use App\Repositories\Client\TemplateRepository;
use App\Repositories\CountryRepository;
use App\Traits\RepoResponse;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    use RepoResponse;

    protected $segmentsRepo;

    protected $repo;

    protected $contactsListRepo;

    protected $country;

    protected $template;

    protected $contactAttributeRepo;

    public function __construct(
        ContactRepository $repo,
        ContactListRepository $contactsListRepo,
        SegmentRepository $segmentsRepo,
        CountryRepository $country,
        TemplateRepository $template,
        ContactAttributeRepository $contactAttributeRepo
    ) {
        $this->contactsListRepo     = $contactsListRepo;
        $this->segmentsRepo         = $segmentsRepo;
        $this->repo                 = $repo;
        $this->country              = $country;
        $this->template             = $template;
        $this->contactAttributeRepo = $contactAttributeRepo;
    }

    public function index(ContactsDataTable $contactsDataTable)
    {
        $countries = $this->country->all();
        $segments  = $this->segmentsRepo->activeSegments();
        $lists     = $this->contactsListRepo->activeList();
        $data      = [
            'segments'  => $segments,
            'lists'     => $lists,
            'countries' => $countries,
            'templates' => $this->template->allTemplates(),
        ];

        return $contactsDataTable->render('backend.client.whatsapp.contacts.index', $data);
    }

    public function turkeyAndIndian()
    {
        $contacts = Contact::whereIn('country_id', ['225', '101'])->get();

        foreach ($contacts as $contact) {
            $segmentId = ($contact->country_id == '225') ? 5 : 3;
            $dd        = ContactRelationSegments::where('contact_id', $contact->id)
                ->where('segment_id', $segmentId)
                ->delete();
            // Insert the new record
            ContactRelationSegments::create([
                'contact_id' => $contact->id,
                'segment_id' => $segmentId,
            ]);
        }
    }

    public function countrySetup()
    {
        $contacts = Contact::whereNull('country_id')->get();
        foreach ($contacts as $contact) {
            $country_id = $this->getCountryByPhoneNumber($contact->phone);
            DB::table('contacts')->where('id', $contact->id)->update([
                'country_id' => $country_id,
            ]);
        }
    }

    private function getCountryByPhoneNumber($phone)
    {
        if (strpos($phone, '+') !== 0) {
            $phone = '+'.$phone;
        }
        $prefixes = Country::pluck('id', 'phonecode');
        if (preg_match('/^\+(\d{1})/', $phone, $matches)) {
            $prefix = $matches[1];
            if (isset($prefixes[$prefix])) {
                return $prefixes[$prefix];
            } elseif (preg_match('/^\+(\d{2})/', $phone, $matches)) {
                $prefix = $matches[1];
                if (isset($prefixes[$prefix])) {
                    return $prefixes[$prefix];
                } elseif (preg_match('/^\+(\d{3})/', $phone, $matches)) {
                    $prefix = $matches[1];
                    if (isset($prefixes[$prefix])) {
                        return $prefixes[$prefix];
                    }
                }
            }
        }

        return null;
    }

    public function getTelegramContact(TelegramSubscriberDataTable $contactsDataTable)
    {
        $groups = BotGroup::active()->withPermission()->pluck('name', 'id');
        $data   = [
            'groups' => $groups,
        ];

        return $contactsDataTable->render('backend.client.telegram.contacts.index', $data);
    }

    public function create()
    {
        try {
            $segments         = $this->segmentsRepo->activeSegments();
            $list             = $this->contactsListRepo->activeList();
            $customAttributes = $this->contactAttributeRepo->all();
            $data             = [
                'segments'         => $segments,
                'lists'            => $list,
                'customAttributes' => $customAttributes,
                'countries'        => $this->country->combo(),
            ];

            return view('backend.client.whatsapp.contacts.create', $data);
        } catch (Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return back();
        }
    }

    public function store(ContactsRequest $request)
    {
        $result = $this->repo->store($request);
        if ($request->ajax()) {
            return $result;
        }
        if ($result->status) {
            return redirect()->route($result->redirect_to)->with($result->redirect_class, $result->message);
        }

        return back()->with($result->redirect_class, $result->message);
    }

    public function edit($id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));

            return back();
        }
        try {
            $list       = $this->contactsListRepo->activeList();
            $segments   = $this->segmentsRepo->activeSegments();
            $contacts   = $this->repo->find($id);
            $attributes = $this->contactAttributeRepo->getAttributesByContactId($id); // Fetch custom attributes
            $data       = [
                'segments'         => $segments,
                'contact'          => $contacts,
                'lists'            => $list,
                'countries'        => $this->country->combo(),
                'customAttributes' => $attributes,
            ];

            return view('backend.client.whatsapp.contacts.edit', $data);
        } catch (Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return back();
        }
    }

    public function view($id)
    {
        return $this->repo->view($id);
    }

    public function update(ContactUpdateRequest $request, $id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));

            return back();
        }

        $result = $this->repo->update($request, $id);
        if ($result->status) {
            return redirect()->route($result->redirect_to)->with($result->redirect_class, $result->message);
        }

        return back()->with($result->redirect_class, $result->message);
    }

    public function updateDetails(Request $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => false,
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }

        return $this->repo->updateDetails($request, $id);
    }

    public function segments(): JsonResponse
    {
        try {
            $segments = $this->segmentsRepo->activeSegments();
            foreach ($segments as $item) {
                $options[] = [
                    'text' => $item->lang_title ?? $item->title,
                    'id'   => $item->id,
                ];
            }

            return response()->json($options);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    // public function contactByClient(Request $request): JsonResponse
    // {
    //     $contacts = $this->repo->activeContacts([
    //         'client_id'   => auth()->user()->client->id,
    //         'type'        => $request->type,
    //         'assignee_id' => $request->assignee_id,
    //         'q'           => $request->q,
    //         'tag_id'      => $request->tag_id,
    //         'is_seen'     => $request->is_seen,
    //     ]);

    //     try {
    //         $data = [
    //             'contacts'      => ContactResource::collection($contacts),
    //             'success'       => true,
    //             'next_page_url' => (bool) $contacts->nextPageUrl(),
    //         ];

    //         return response()->json($data);
    //     } catch (Exception $e) {
    //         return response()->json(['status' => false, 'error' => __('something_went_wrong_please_try_again')]);
    //     }
    // }

    public function contactByClient(Request $request): JsonResponse
    {
        $user = auth()->user();
        $adminId = session('admin_id');
        
        $adminUser = $adminId ? \App\Models\User::find($adminId) : null;

        $hasGlobalInbox = in_array('global_inbox', $user->permissions ?? []);


        if ($adminUser) {
            $hasGlobalInbox = true;
        }

        // Build shared filter parameters
        $filters = [
            'client_id'   => $user->client->id,
            'type'        => $request->type,
            'assignee_id' => $request->assignee_id,
            'q'           => $request->q,
            'tag_id'      => $request->tag_id,
            'is_seen'     => $request->is_seen,
        ];
 
        // Fetch contacts based on permission
        $contacts = $hasGlobalInbox
            ? $this->repo->activeContacts($filters)
            : $this->repo->activeContactsTeamWise($filters);

        // Prepare response
        try {
            return response()->json([
                'contacts'      => ContactResource::collection($contacts),
                'success'       => true,
                'next_page_url' => (bool) $contacts->nextPageUrl(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status' => false,
                'error'  => __('something_went_wrong_please_try_again'),
            ], 500);
        }
    }

    public function addBlacklist(Request $request)
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
            $this->repo->blacklist($request);
            $data = [
                'status'  => 200,
                'message' => __('update_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function removeBlacklist(Request $request)
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
            $this->repo->removeBlacklist($request);
            $data = [
                'status'  => 200,
                'message' => __('remove_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function addList(Request $request)
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
            $this->repo->addContactList($request);
            $data = [
                'status'  => 200,
                'message' => __('add_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function addSegment(Request $request)
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
            $this->repo->addSegment($request);
            $data = [
                'status'  => 200,
                'message' => __('add_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function removeList(Request $request)
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
            $this->repo->removeContactList($request);
            $data = [
                'status'  => 200,
                'message' => __('remove_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function delete($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }

        return $this->repo->destroy($id);
    }

    public function bulkDelete(Request $request)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }

        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:contacts,id',
        ]);

        return $this->repo->bulkDelete($request);
    }

    public function removeSegment(Request $request)
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
            $this->repo->removeSegment($request);
            $data = [
                'status'  => 200,
                'message' => __('remove_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function statusChange(Request $request): \Illuminate\Http\JsonResponse
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
            $total_contacts = Contact::where('client_id', auth()->user()->client_id)->where('status', 1)->count();
            $client         = auth()->user()->client;
            $contacts_limit = $client->activeSubscription->contact_limit;

            if ($total_contacts >= $contacts_limit) {
                $data = [
                    'status'  => 'danger',
                    'message' => __('insufficient_contacts_limit'),
                    'title'   => 'error',
                ];

                return response()->json($data);
            }
            $this->repo->statusChange($request->all());
            $data           = [
                'status'  => 200,
                'message' => __('update_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        } catch (Exception $e) {
            $data = [
                'status'  => 400,
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
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
            $response = $this->repo->addBlock($id);

            $data     = [
                'status'  => 'success',
                'message' => __($response['message']),
                'title'   => __('success'),
            ];

            return response()->json($data);
        } catch (Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => $e->getMessage(),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function unblock($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
            $response = $this->repo->removeBlock($id);

            $data     = [
                'status'  => 'success',
                'message' => __($response['message']),
                'title'   => __('success'),
            ];

            return response()->json($data);
        } catch (Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => $e->getMessage(),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function createImport()
    {
        $segments = Segment::select('id', 'title as name')->withPermission()->get();
        $list     = ContactsList::select('id', 'name')->withPermission()->get();
        $data     = [
            'segments' => $segments,
            'lists'    => $list,
        ];

        return view('backend.client.whatsapp.import.create', $data);
    }

    public function parseCSV(Request $request)
    {
        $rules     = [
            'file' => 'required|file|mimes:xlsx|max:10240',
        ];
        $messages  = [
            'file.required' => 'Please upload a file.',
            'file.mimes'    => 'The uploaded file must be an Excelfile.',
            'file.max'      => 'The file size must be 10 MB or less.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->formatResponse(false, $validator->errors(), 'client.contacts.index', []);
        }

        return $this->repo->parseCSV($request);
    }

    public function confirmUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->repo->confirmUpload($request);
    }

    public function getContactDownload(Request $request)
    {
        $query    = Contact::query()->with(['contactList', 'segmentList'])
            ->where('contacts.client_id', auth()->user()->client->id)
            ->withPermission();
        $query->when($request->filled('country_id'), function ($query) use ($request) {
            $query->where('country_id', $request->country_id);
        });
        if ($request->filled('contact_list_id') && $request->contact_list_id !== 'all') {
            $contactListIds = is_array($request->contact_list_id) ? $request->contact_list_id : [$request->contact_list_id];
            $query->whereHas('contactLists', function ($q) use ($contactListIds) {
                $q->whereIn('contact_list_id', $contactListIds);
            });
        }
        if ($request->filled('segments_id') && $request->segments_id !== 'all') {
            $segmentIds = is_array($request->segments_id) ? $request->segments_id : [$request->segments_id];
            $query->whereHas('segments', function ($q) use ($segmentIds) {
                $q->whereIn('segment_id', $segmentIds);
            });
        }
        $query->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        });
        $query->when($request->filled('phone'), function ($query) use ($request) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        });
        $query->when($request->filled('is_blacklist'), function ($query) use ($request) {
            $query->where('is_blacklist', $request->is_blacklist);
        });
        $query->where('type', TypeEnum::WHATSAPP);
        if ($request->filled('date_range')) {
            $dateRange = $this->parseDate($request->date_range);
            $query->whereBetween('created_at', $dateRange);
        }
        $filename = 'filtered_contacts_'.now()->format('YmdHis').'.xlsx';

        return Excel::download(new FilteredContact($query), $filename);
    }

    private function parseDate($date_range)
    {
        $dates      = explode('to', $date_range);

        if (count($dates) == 1) {
            $dates[1] = $dates[0];
        }

        $start_date = trim($dates[0]);
        $end_date   = trim($dates[1]);

        $start_date = $start_date.' 00:00:00';
        $end_date   = $end_date.' 23:59:59';

        return [
            Carbon::parse($start_date)->format('Y-m-d H:s:i'),
            Carbon::parse($end_date)->format('Y-m-d H:s:i'),
        ];
    }
}
