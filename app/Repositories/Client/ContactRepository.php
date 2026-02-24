<?php

namespace App\Repositories\Client;

use App\Enums\MessageStatusEnum;
use App\Enums\TypeEnum;
use App\Models\ClientStaff;
use App\Models\Contact;
use App\Models\ContactAttributeValue;
use App\Models\ContactNote;
use App\Models\ContactRelationList;
use App\Models\ContactRelationSegments;
use App\Models\ContactsList;
use App\Models\Country;
use App\Models\Message;
use App\Models\Segment;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Traits\ContactTrait;
use App\Traits\ImageTrait;
use App\Traits\RepoResponse;
use App\Traits\SimpleXLSX;
use App\Traits\TelegramTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContactRepository
{
    use ContactTrait, ImageTrait, RepoResponse, TelegramTrait;

    private $model;

    private $country;

    private $segment;

    private $contactsList;

    private $staff;

    public function __construct(
        Contact $model,
        Country $country,
        Segment $segment,
        ContactsList $contactsList,
        ClientStaff $staff
    ) {
        $this->model        = $model;
        $this->country      = $country;
        $this->segment      = $segment;
        $this->contactsList = $contactsList;
        $this->staff        = $staff;
    }

    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    public function all($request)
    {
        return $this->model->latest()->withPermission()->where('type', $request->type)->paginate(setting('pagination'));
    }

    public function blockContacts($request)
    {
        return $this->model->latest()->withPermission()->where('type', $request->type)->where('is_blacklist', 1);
    }

    public function getChatContactList(array $data = []): LengthAwarePaginator
    {
        $query = $this->model->with(['lastMessage'])
            ->select('contacts.*')
            ->withPermission()
            ->where('has_conversation', 1)
            ->where('contacts.status', 1)
            ->where('is_blacklist', 0);

        // Apply search query
        if (! empty($data['q'])) {
            $query->where(function ($q) use ($data) {
                $q->where('name', 'like', '%'.$data['q'].'%')
                    ->orWhere('phone', 'like', '%'.$data['q'].'%');
            });
        }

        // Apply type filter
        if (! empty($data['type'])) {
            $query->where('type', $data['type']);
        }
        //        $query->whereHas('lastMessage', function ($q) use ($data) {
        //            $q->where('status', $data['is_seen']);
        //        });

        // Apply assignee_id filter
        if (! empty($data['assignee_id'])) {
            $query->where('assignee_id', $data['assignee_id']);
        }

        // Apply tag_id filter
        if (isset($data['is_seen'])) {
            $query->where('has_unread_conversation', (int) $data['is_seen']);
        }

        // Apply tag_id filter
        if (! empty($data['tag_id'])) {
            $query->join('contact_tags', 'contact_tags.contact_id', '=', 'contacts.id')
                ->where('contact_tags.tag_id', $data['tag_id']);
        }

        // Order results and paginate
        return $query->orderBy('last_conversation_at', 'DESC')
            ->paginate(20);
    }

    public function getChatContactListTeamWise(array $data = []): LengthAwarePaginator
    {
        $query = $this->model->with(['lastMessage'])
            ->select('contacts.*')
            ->withPermission()
            ->where('has_conversation', 1)
            ->where('contacts.status', 1)
            ->where('assignee_id', auth()->id())
            ->where('is_blacklist', 0);

        // Apply search query
        if (! empty($data['q'])) {
            $query->where(function ($q) use ($data) {
                $q->where('name', 'like', '%'.$data['q'].'%')
                    ->orWhere('phone', 'like', '%'.$data['q'].'%');
            });
        }

        // Apply type filter
        if (! empty($data['type'])) {
            $query->where('type', $data['type']);
        }
        //        $query->whereHas('lastMessage', function ($q) use ($data) {
        //            $q->where('status', $data['is_seen']);
        //        });

        // Apply assignee_id filter
        if (! empty($data['assignee_id'])) {
            $query->where('assignee_id', $data['assignee_id']);
        }

        // Apply tag_id filter
        if (isset($data['is_seen'])) {
            $query->where('has_unread_conversation', (int) $data['is_seen']);
        }

        // Apply tag_id filter
        if (! empty($data['tag_id'])) {
            $query->join('contact_tags', 'contact_tags.contact_id', '=', 'contacts.id')
                ->where('contact_tags.tag_id', $data['tag_id']);
        }

        // Order results and paginate
        return $query->orderBy('last_conversation_at', 'DESC')
            ->paginate(20);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $client               = auth()->user()->client;
            $total_contacts       = Contact::where('client_id', auth()->user()->client_id)->where('status', 1)->count();

            $client               = auth()->user()->client;
            $activeSubscription   = $client->activeSubscription;

            if (! $activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.contacts.index', []);
            }
            $existingContactCount = $this->model->where('client_id', $client->id)->count();
            if ($activeSubscription->contact_limit != -1 && $existingContactCount >= $activeSubscription->contact_limit) {
                return $this->formatResponse(false, __('insufficient_contacts_limit'), 'client.contacts.index', []);
            }

            $response['images']   = '';
            if (isset($request['images'])) {
                $requestImage = $request['images'];
                $response     = $this->saveImage($requestImage, '_contact_');
            }

            $contact              = new $this->model;
            $contact->name        = $request->name;
            $contact->phone       = str_replace(' ', '', $request->phone);
            $contact->country_id  = $request->country_id;
            $contact->client_id   = Auth::user()->client->id;
            $contact->status      = $request->status ?? 1;
            $contact->images      = $response['images'];
            $contact->save();

            // Save custom attributes
            if ($request->has('custom_attributes')) {
                foreach ($request->custom_attributes as $attributeId => $value) {
                    $contactAttributeValue               = new ContactAttributeValue;
                    $contactAttributeValue->contact_id   = $contact->id;
                    $contactAttributeValue->attribute_id = $attributeId;
                    $contactAttributeValue->attr_value   = $value;
                    $contactAttributeValue->save();
                }
            }

            if (! empty($request->contact_list_id) && is_array($request->contact_list_id)) {
                foreach ($request->contact_list_id as $list_id) {
                    $contactRelationList                  = new ContactRelationList;
                    $contactRelationList->contact_id      = $contact->id;
                    $contactRelationList->contact_list_id = $list_id;
                    $contactRelationList->save();
                }
            } else {
                $contactList = $this->getOrCreateContactList(auth()->user()->client);
                $this->establishContactListRelations($contact, $contactList);
            }

            if (! empty($request->segment_id) && is_array($request->segment_id)) {
                foreach ($request->segment_id as $segment) {
                    $contactRelationSegment             = new ContactRelationSegments;
                    $contactRelationSegment->contact_id = $contact->id;
                    $contactRelationSegment->segment_id = $segment;
                    $contactRelationSegment->save();
                }
            } else {
                $defaultSegment = $this->getOrCreateDefaultSegment(auth()->user()->client);
                $this->establishContactSegmentRelations($contact, $defaultSegment);
            }
            DB::commit();

            return $this->formatResponse(true, __('created_successfully'), 'client.contacts.index', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                logError('Upload Contact: ', $e);
            }

            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.contacts.index', []);
        }
    }

    public function find($id)
    {
        return $this->model->withPermission()->with('contactList.list', 'segmentList.segment')->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        
        try {
            $client              = auth()->user()->client;
            $activeSubscription  = $client->activeSubscription;
            if (! $activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.contacts.index', []);
            }
            if (isset($request['images'])) {
                $requestImage = $request['images'];
                $response     = $this->saveImage($requestImage, '_contact_');
            }
            $contact             = Contact::findOrFail($id);
            $contact->name       = $request->name;
            $contact->phone      = str_replace(' ', '', $request->phone);
            $contact->country_id = $request->country_id;
            $contact->client_id  = Auth::user()->client_id;
            $contact->status     = $request->status    ?? 1;
            $contact->images     = $response['images'] ?? $contact->images;
            $contact->save();

            // Update custom attributes
            $attributes          = $request->input('attributes', []);
            if (is_array($attributes)) {  // Check if $attributes is an array
                foreach ($attributes as $attributeId => $value) {
                    $this->updateContactAttribute($contact->id, $attributeId, $value);
                }
            }
            ContactRelationList::whereIn('contact_id', [$id])->delete();

            if (! empty($request->contact_list_id)) {
                foreach ($request->contact_list_id as $list_id) {
                    $contactRelationList                  = new ContactRelationList;
                    $contactRelationList->contact_id      = $contact->id;
                    $contactRelationList->contact_list_id = $list_id;
                    $contactRelationList->save();
                }
            } else {
                // $contactList = $this->getOrCreateContactList(auth()->user()->client);
                // $this->establishContactListRelations($contact, $contactList);
            }

            ContactRelationSegments::whereIn('contact_id', [$id])->delete();

            if (! empty($request->segments)) {
                foreach ($request->segments as $segment) {
                    $contactRelationSegment             = new ContactRelationSegments;
                    $contactRelationSegment->contact_id = $contact->id;
                    $contactRelationSegment->segment_id = $segment;
                    $contactRelationSegment->save();
                }
            } else {
                // $defaultSegment = $this->getOrCreateDefaultSegment(auth()->user()->client);
                // $this->establishContactSegmentRelations($contact, $defaultSegment);
            }

            DB::commit();

            return $this->formatResponse(true, __('updated_successfully'), 'client.contacts.index', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
                logError('Upload Contact: ', $e);
            }

            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.contacts.index', []);
        }
    }

    public function updateDetails($request, $id)
    {
        DB::beginTransaction();
        try {
            if (isset($request['images'])) {
                $requestImage = $request['images'];
                $response     = $this->saveImage($requestImage, '_contact_');
            }
            $contact               = Contact::findOrFail($id);

            $contact->country_id   = $request->country_id;
            $contact->assignee_id  = $request->assignee_id;
            if ($request->email) {
                $contact->email = $request->email;
            }
            $contact->address      = $request->address;
            $contact->city         = $request->city;
            $contact->state        = $request->state;
            $contact->zipcode      = $request->zipcode;
            $contact->birthdate    = $request->birthdate;
            $contact->gender       = $request->gender;
            $contact->occupation   = $request->occupation;
            $contact->company      = $request->company;
            $contact->bot_reply    = $request->bot_reply ? 1 : 0;
            $contact->is_blacklist = $request->is_blacklist ? 1 : 0;
            $contact->images       = $response['images'] ?? $contact->images;
            $contact->save();

            if (! empty($request->contact_list_id)) {
                ContactRelationList::whereIn('contact_id', [$id])->delete();
                foreach ($request->contact_list_id as $list_id) {
                    $contactRelationList                  = new ContactRelationList;
                    $contactRelationList->contact_id      = $contact->id;
                    $contactRelationList->contact_list_id = $list_id;
                    $contactRelationList->save();
                }
            } else {
                $contactList = $this->getOrCreateContactList(auth()->user()->client);
                $this->establishContactListRelations($contact, $contactList);
            }

            if (! empty($request->segment_id)) {
                ContactRelationSegments::whereIn('contact_id', [$id])->delete();
                foreach ($request->segment_id as $segment) {
                    $contactRelationSegment             = new ContactRelationSegments;
                    $contactRelationSegment->contact_id = $contact->id;
                    $contactRelationSegment->segment_id = $segment;
                    $contactRelationSegment->save();
                }
            } else {
                $defaultSegment = $this->getOrCreateDefaultSegment(auth()->user()->client);
                $this->establishContactSegmentRelations($contact, $defaultSegment);
            }

            DB::commit();

            return $this->formatResponse(true, __('updated_successfully'), 'client.contacts.index', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }

            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.contacts.index', []);
        }
    }

    public function blacklist($request)
    {
        $ids       = $request->ids;
        $blacklist = $request->is_blacklist;
        Contact::whereIn('id', $ids)->update(['is_blacklist' => $blacklist]);
    }

    public function removeBlacklist($request)
    {
        $ids       = $request->ids;
        $blacklist = $request->is_blacklist;

        Contact::whereIn('id', $ids)->update(['is_blacklist' => $blacklist]);
    }

    public function activeContacts($data)
    {
        return $this->model->where('status', 1)
            ->where('contacts.client_id', auth()->user()->client->id)
            ->when(arrayCheck('client_id', $data), function ($query) use ($data) {
                $query->where('client_id', $data['client_id']);
            })
            ->when(arrayCheck('q', $data), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('name', 'LIKE', '%' . $data['q'] . '%')
                      ->orWhere('phone', 'LIKE', '%' . $data['q'] . '%');
                });
            })
            ->when(arrayCheck('type', $data), function ($query) use ($data) {
                $query->where('type', $data['type']);
            })
            ->when(arrayCheck('assignee_id', $data), function ($query) use ($data) {
                $query->where('assignee_id', $data['assignee_id']);
            })
            ->when(arrayCheck('tag_id', $data), function ($query) use ($data) {
                $query->whereHas('tags', function ($q) use ($data) {
                    $q->where('id', $data['tag_id']);
                });
            })
            ->when(array_key_exists('is_seen', $data) && $data['is_seen'] !== null, function ($query) use ($data) {
                $query->where('has_unread_conversation', (int) $data['is_seen']); 
            })
            ->with(['lastMessage'])
            ->withPermission()
            ->where('contacts.is_blacklist', 0)
            ->orderBy('name')
            ->paginate(20);
    }

    public function activeContactsTeamWise($data)
    {
        return $this->model->where('status', 1)
            ->where('contacts.client_id', auth()->user()->client->id)
            ->when(arrayCheck('client_id', $data), function ($query) use ($data) {
                $query->where('client_id', $data['client_id']);
            })
            ->when(arrayCheck('q', $data), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('name', 'LIKE', '%' . $data['q'] . '%')
                      ->orWhere('phone', 'LIKE', '%' . $data['q'] . '%');
                });
            })
            ->when(arrayCheck('type', $data), function ($query) use ($data) {
                $query->where('type', $data['type']);
            })
            ->when(arrayCheck('assignee_id', $data), function ($query) use ($data) {
                $query->where('assignee_id', $data['assignee_id']);
            })
            ->when(arrayCheck('tag_id', $data), function ($query) use ($data) {
                $query->whereHas('tags', function ($q) use ($data) {
                    $q->where('id', $data['tag_id']);
                });
            })
            ->when(array_key_exists('is_seen', $data) && $data['is_seen'] !== null, function ($query) use ($data) {
                $query->where('has_unread_conversation', (int) $data['is_seen']); 
            })
            ->with(['lastMessage'])
            ->withPermission()
            ->where('contacts.is_blacklist', 0)
            ->where('assignee_id', auth()->id())
            ->orderBy('name')
            ->paginate(20);
    }



    public function addContactList(Request $request)
    {
        $ids = $request->ids;
        ContactRelationList::whereIn('contact_id', $ids)->delete();

        foreach ($ids as $id) {
            $contactRelationList                  = new ContactRelationList;
            $contactRelationList->contact_id      = $id;
            $contactRelationList->contact_list_id = $request->contact_list_id;
            $contactRelationList->save();
        }
    }

    public function addSegment(Request $request)
    {
        $ids = $request->ids;
        ContactRelationSegments::whereIn('contact_id', $ids)->delete();

        foreach ($ids as $id) {
            $contactSegments             = new ContactRelationSegments;
            $contactSegments->contact_id = $id;
            $contactSegments->segment_id = $request->segment_id;
            $contactSegments->save();
        }
    }

    public function removeContactList($request)
    {
        $ids = $request->ids;
        ContactRelationList::whereIn('contact_id', $ids)->update(['contact_list_id' => null]);
    }

    public function removeSegment($request)
    {
        $ids = $request->ids;
        ContactRelationSegments::whereIn('contact_id', $ids)->update(['segment_id' => null]);
    }

    public function readRatePercentage($request)
    {
        $delivered_message = Message::where('client_id', Auth::user()->client->id)->where('source', TypeEnum::WHATSAPP)->whereNotNull('campaign_id')->whereIn('status', [MessageStatusEnum::DELIVERED, MessageStatusEnum::READ])->count();
        $read_message      = Message::where('client_id', Auth::user()->client->id)->where('source', TypeEnum::WHATSAPP)->whereNotNull('campaign_id')->where('status', MessageStatusEnum::READ)->count();
        if ($delivered_message > 0) {
            $readRatePercentage = ($read_message / $delivered_message) * 100;
        } else {
            $readRatePercentage = 0;
        }

        return number_format($readRatePercentage, 0);
    }

    public function statusChange($request)
    {
        $id = $request['id'];

        return Contact::find($id)->update($request);
    }

    public function addBlock($id)
    {
        $contact               = Contact::findOrfail($id);
        $contact->is_blacklist = 1;
        $contact->save();
        $data                  = [
            'status'  => true,
            'message' => __('successfully_blacklisted'),
        ];

        return $data;
    }

    public function removeBlock($id)
    {
        $contact               = Contact::findOrfail($id);
        $contact->is_blacklist = 0;
        $contact->save();
        $data                  = [
            'status'  => true,
            'message' => __('successful_remove_blacklist'),
        ];

        return $data;
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            DB::table('contact_relation_segments')->where('contact_id', $id)->delete();
            // DB::table('contact_notes')->where('contact_id', $id)->delete();
            DB::table('contact_relation_lists')->where('contact_id', $id)->delete();
            DB::table('messages')->where('contact_id', $id)->delete();
            $contact = $this->model->find($id);
            if ($contact) {
                $contact->delete();
                DB::commit();

                return $this->formatResponse(true, __('deleted_successfully'), 'client.contacts.index', []);
            } else {
                return $this->formatResponse(false, __('contact_not_found'), 'client.contacts.index', []);
                throw new \Exception(__('contact_not_found'));
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();

            return $this->formatResponse(false, $e->getMessage(), 'client.contacts.index', []);
        }
    }

    public function bulkDelete($request)
    {
        $ids = $request->ids;
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                DB::table('contact_relation_segments')->where('contact_id', $id)->delete();
                DB::table('contact_notes')->where('contact_id', $id)->delete();
                DB::table('contact_relation_lists')->where('contact_id', $id)->delete();
                DB::table('messages')->where('contact_id', $id)->delete();
                $contact = $this->model->find($id);
                if ($contact) {
                    $contact->delete();
                } else {
                    throw new \Exception('Contact not found with ID: '.$id);
                }
            }
            DB::commit();

            return $this->formatResponse(true, __('deleted_successfully'), 'client.contacts.index', []);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->formatResponse(false, $e->getMessage(), 'client.contacts.index', []);
        }
    }

    public function view(int $id)
    {
        try {
            $contact   = $this->model->with(['contact_flow', 'tags'])->withPermission()->find($id);
            $staffs    = User::where('client_id', Auth::user()->client->id)->get();
            $countries = $this->country->active()->pluck('name', 'id');
            $segments  = $this->segment->active()->withPermission()->pluck('title', 'id');
            $lists     = $this->contactsList->active()->withPermission()->pluck('name', 'id');
            $data      = [
                'staffs'    => $staffs,
                'contact'   => $contact,
                'segments'  => $segments,
                'lists'     => $lists,
                'countries' => $countries,
                'notes'     => ContactNote::where('contact_id', $id)->get(),
            ];
            $result    = view('backend.client.whatsapp.contacts.modal.__contact_body', $data)->render();

            return $this->formatResponse(true, __('data_found'), 'client.contacts.index', $result);
        } catch (\Throwable $e) {
            logError('Upload Contact: ', $e);

            return $this->formatResponse(false, $e->getMessage(), 'client.contacts.index', []);
        }
    }

    public function parseCSV($request)
    {
        try {
            $file     = $request->file('file');
            $xlsx     = SimpleXLSX::parse($file);
            $rows     = $xlsx->rows();
            unset($rows[0]);
            $all_rows = [];
            foreach ($rows as $row) {
                $all_rows[] = [
                    $row[0],
                    $row[1],
                ];
            }
            $data     = [
                'rows' => $all_rows,
            ];

            return $this->formatResponse(true, __('data_found'), 'client.contact.create', $data);
        } catch (\Exception $e) {
            return $this->formatResponse(false, $e->getMessage(), 'client.contact.create', []);
        }
    }

    public function confirmUpload($request)
    {
        $client               = auth()->user()->client;
        $activeSubscription   = $client->activeSubscription;
        if (! $activeSubscription) {
            return $this->formatResponse(false, __('no_active_subscription'), 'client.contacts.index', []);
        }
        $existingContactCount = $this->model->where('client_id', $client->id)->count();

        $whatsappService      = new WhatsAppService;
        try {
            $data   = json_decode($request->data);
            $array  = array_map('array_filter', $data);
            $data   = array_filter($array);
            if (! $data) {
                return response()->json([
                    'status'  => false,
                    'message' => __('no_row_found'),
                ]);
            }
            if (count($data) == 0) {
                return response()->json([
                    'status'  => false,
                    'message' => __('no_row_found'),
                ]);
            }
            if (count($data) > 0) {
                $rows           = [0, 1];
                //finding the empty rows
                $filled_rows    = [];
                $cells          = [];
                foreach ($data as $key => $datum) {
                    $cells[] = $key;
                    foreach ($datum as $row_key => $value) {
                        $filled_rows[$key][] = $row_key ?? null;
                    }
                }
                $validated_rows = [];

                foreach ($cells as $key => $cell) {
                    $diff_array = array_diff($rows, $filled_rows[$key]);
                    foreach ($diff_array as $item) {
                        if (! in_array($item, [0])) {
                            $validated_rows[] = [
                                'x' => $item,
                                'y' => $cell,
                            ];
                        }
                    }
                }
                if (count($validated_rows) > 0) {

                    return response()->json([
                        'status'  => false,
                        'rows'    => $validated_rows,
                        'message' => __('required_fields_are_missing'),
                        'errors'  => [],
                    ], 422);
                }
            }
            $errors = [];
            DB::beginTransaction();
            foreach ($data as $row) {
                if (isset($row[1])) {
                    $normalizedPhone = $this->normalizePhone($row[1]);
                    if ($activeSubscription->contact_limit != -1 && $existingContactCount >= $activeSubscription->contact_limit) {
                        return response()->json([
                            'success' => false,
                            'message' => __('insufficient_contacts_limit'),
                        ]);
                    }
                    // $contact        = Contact::where('client_id', auth()->user()->client->id)->where('phone', $row[1])->orWhere('phone', '+' . $row[1])->first();
                    $contact         = Contact::where('client_id', auth()->user()->client->id)
                        ->where('phone', $normalizedPhone)
                        ->first();
                    $country_id      = $whatsappService->extractCountryCode($row[1]);
                    if (empty($contact)) {
                        $contact             = new Contact;
                        $contact->name       = $row['0'];
                        $contact->phone      = $normalizedPhone;
                        $contact->country_id = $country_id;
                        $contact->client_id  = auth()->user()->client->id;
                        $contact->save();
                        $existingContactCount++;
                    }
                    $contactListIds  = array_filter(explode(';', $row[2] ?? ''), fn ($v) => ! empty($v));
                    if (! empty($contactListIds)) {
                        foreach ($contactListIds as $list_id) {
                            if (! empty($list_id)) {
                                ContactRelationList::firstOrCreate([
                                    'contact_id'      => $contact->id,
                                    'contact_list_id' => $list_id,
                                ]);
                            }
                        }
                    } else {
                        $contactList = ContactsList::firstOrCreate([
                            'client_id' => auth()->user()->client->id,
                            'name'      => 'Uncategorized',
                        ]);
                        ContactRelationList::firstOrCreate([
                            'contact_id'      => $contact->id,
                            'contact_list_id' => $contactList->id,
                        ]);
                    }
                    $segmentIds      = array_filter(explode(';', $row[3] ?? ''), fn ($v) => ! empty($v));
                    if (! empty($segmentIds)) {
                        foreach ($segmentIds as $segmentId) {
                            if (! empty($segmentId)) {
                                ContactRelationSegments::firstOrCreate([
                                    'contact_id' => $contact->id,
                                    'segment_id' => $segmentId,
                                ]);
                            }
                        }
                    } else {
                        $defaultSegment = Segment::firstOrCreate([
                            'client_id' => auth()->user()->client->id,
                            'title'     => 'Default',
                        ], [
                            'client_id' => auth()->user()->client->id,
                            'title'     => 'Default',
                        ]);
                        ContactRelationSegments::firstOrCreate([
                            'contact_id' => $contact->id,
                            'segment_id' => $defaultSegment->id,
                        ]);
                    }
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('created_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            logError('Upload Contact: ', $e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/\D/', '', $phone);
    }

    protected function updateContactAttribute($contactId, $attributeId, $value)
    {
        // Find or create the contact attribute value record
        ContactAttributeValue::updateOrCreate(
            [
                'contact_id'   => $contactId,
                'attribute_id' => $attributeId,
            ],
            [
                'attr_value' => $value,
            ]
        );
    }
}
