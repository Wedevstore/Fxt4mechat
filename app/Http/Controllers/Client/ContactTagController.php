<?php
namespace App\Http\Controllers\Client;
use App\Models\ClientTag;
use App\Models\ContactTag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TagResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ContactTagResource;
use App\Models\Contact;

class ContactTagController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([ 
                'status' => true,
                'success' => __('tag_retrived_successfully'),
                'tags'    => TagResource::collection(ClientTag::orderBy('id',"DESC")->withPermission()->get()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false,'error' => $e->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'contact_id' => 'required|exists:contacts,id',
                'title'      => 'required|string|max:255',
            ]);
            $existingTag = DB::table('client_tags')
                ->where('client_id', Auth::user()->client->id)
                ->where('title', $request->title)
                ->first();
    
            if ($existingTag) {
                return response()->json([
                    'status' => false,
                    'error'  => __('tag_already_exists'),
                ]);
            }
            $clientTagId = DB::table('client_tags')->insertGetId([
                'client_id' => Auth::user()->client->id,
                'title'     => $request->title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             ContactTag::create([
                'contact_id' => $request->contact_id,
                'tag_id'     => $clientTagId,
                'status'     => 1,
            ]);
            $tags = ContactTag::where('contact_id', $request->contact_id)
                              ->orderBy('id', 'DESC')
                              ->get();
                return response()->json([
                'status'  => true,
                'success' => __('tag_added_successfully'),
                'tags'    => ContactTagResource::collection($tags),
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage(),
            ]);
        }
    }
    

    public function storeContactTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|integer|exists:contacts,id',
            'ids' => 'sometimes|array', // Updated to sometimes to allow empty ids
            'ids.*' => 'integer|exists:client_tags,id',
        ]);
    
        try {
            DB::transaction(function () use ($validated) {
                // Delete existing tags for the contact
                ContactTag::where('contact_id', $validated['contact_id'])->delete();
    
                // Check if 'ids' is provided and is not empty
                if (!empty($validated['ids'])) {
                    // Prepare new tags data
                    $newTags = collect($validated['ids'])->map(function ($tagId) use ($validated) {
                        return [
                            'contact_id' => $validated['contact_id'],
                            'tag_id' => $tagId,
                            'status' => 1,
                        ];
                    })->toArray();
    
                    // Insert new tags if any
                    if (!empty($newTags)) {
                        ContactTag::insert($newTags);
                    }
                }
            });
    
            // Fetch the updated tags after insertion (if any) or deletion
            $updatedTags = ContactTagResource::collection(ContactTag::where('contact_id', $validated['contact_id'])->orderBy('id', "DESC")->get());
    
            return response()->json([
                'status' => true,
                'success' => __('tag_status_changed_successfully'),
                'tags' => $updatedTags,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getContactTags(Request $request): JsonResponse
    {

        $client = ContactTagResource::collection(
            ContactTag::where('contact_id', $request->chat_room_id)
                ->orderBy('id', "DESC")
                ->get()
        );

        if ($client->isNotEmpty()) {
            $contact_id = $client->first()->resource->contact_id ?? null; // Ensure contact_id is not null

            if ($contact_id) { // Check if contact_id is not null
                $contact = Contact::find($contact_id);
                if ($contact) {
                    $contact->has_unread_conversation = 0;
                    $contact->save();
                }
            }
        }

        try {
            return response()->json([ 
                'status' => true,
                'success' => __('data_found'),
                'tags'    => ContactTagResource::collection(ContactTag::where('contact_id',$request->chat_room_id)->orderBy('id',"DESC")->get()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false,'error' => $e->getMessage()]);
        }
    }
}
