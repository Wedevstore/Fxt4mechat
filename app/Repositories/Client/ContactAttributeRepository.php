<?php
namespace App\Repositories\Client;
use App\Traits\RepoResponse;
use App\Models\ContactAttribute;
use Illuminate\Support\Facades\Auth;
class ContactAttributeRepository
{
    use RepoResponse;

    private $model;

    public function __construct(ContactAttribute $model)
    {
        $this->model = $model;
    }

    public function all($with = [])
    {
        return  ContactAttribute::where('status', '1')->withPermission()->get();
    }

    public function store($request)
    {

        try {
            $fields = new $this->model();
            $fields->title = $request->title;
            $fields->type = $request->type;
            $fields->client_id = Auth::user()->client->id;
            $fields->status = $request->status;
            $fields->save();
            return $this->formatResponse(true, __('created_successfully'), route('client.contact-attributes.index'), []);
        } catch (\Throwable $e) {
            logError('Throwable: ', $e);
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            return $this->formatResponse(false, $e->getMessage(), '', []);
        }
    }


    public function find($id)
    {
        try {
            return $this->model->withPermission()->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception(__('contact_attribute_not_found'));
        }
    }

 
    public function view($id)
    {
        try {
            $contactAttribute = $this->find($id);
            return $this->formatResponse(true, __('created_successfully'), 'client.contact-attributes.index', []);
        } catch (\Exception $e) {
            return $this->formatResponse(false, $e->getMessage(), 'client.contact-attributes.index', []);
        }
    }

    public function update($request, $id)
    {
        try {
            $contactAttribute = $this->find($id);
            $contactAttribute->title = $request->title;
            $contactAttribute->type = $request->type;
            $contactAttribute->status = $request->status;
            $contactAttribute->save();
            return $this->formatResponse(true, __('created_successfully'), route('client.contact-attributes.index'), []);
        } catch (\Exception $e) {
            logError('Throwable: ', $e);
            return $this->formatResponse(false, $e->getMessage(), '', []);
        }
    }

    public function destroy($id)
    {
        try {
            $contactAttribute = $this->find($id);
            $contactAttribute->delete();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.contact-attributes.index', []);
        } catch (\Exception $e) {
            logError('Throwable: ', $e);
            return $this->formatResponse(false, $e->getMessage(), 'client.contact-attributes.index', []);
        }
    }

    public function getAttributesByContactId(int $contactId)
    {
        return ContactAttribute::where('client_id', Auth::user()->client->id)->withPermission()->get();
    }
}
