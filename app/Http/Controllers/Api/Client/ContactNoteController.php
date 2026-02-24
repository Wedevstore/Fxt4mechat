<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoteResource;
use App\Models\ContactNote;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactNoteController extends Controller
{
    use ApiReturnFormatTrait;
    
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $data = [
                'notes' => NoteResource::collection(ContactNote::where('contact_id', request()->contact_id)->orderBy('id','DESC')->get()),
            ];
            return $this->responseWithSuccess('note_retrieved_successfully', $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'contact_id' => 'required|exists:contacts,id',
                'title'      => 'required|string',
                'details'    => 'required|string',
            ]);

            ContactNote::create([
                'contact_id' => $request->contact_id,
                'title'      => $request->title,
                'details'    => $request->details,
            ]);

            return $this->responseWithSuccess('note_added_successfully');

        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'title'   => 'required|string',
                'details' => 'required|string',
            ]);

            $note = ContactNote::find($id);
            $note->update([
                'title'   => $request->title,
                'details' => $request->details,
            ]);
            return $this->responseWithSuccess('note_updated_successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $note = ContactNote::find($id);
            $note->delete();
            return $this->responseWithSuccess('note_deleted_successfully');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
