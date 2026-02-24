<?php
namespace App\Http\Controllers\Client;
use Exception;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\DataTables\Client\ContactAttributeDataTable;
use App\Http\Requests\Client\ContactAttributeRequest;
use App\Repositories\Client\ContactAttributeRepository;

class ContactAttributeController extends Controller
{
    protected $repo;

    public function __construct(ContactAttributeRepository $repo)
    { 
        $this->repo = $repo;
    }

    public function index(ContactAttributeDataTable $contactAttrDataTable)
    {
        try {
            return $contactAttrDataTable->render('backend.client.whatsapp.contacts.attribute.index');
        } catch (Exception $e) {
            Toastr::error(__('something_went_wrong_please_try_again'));
            return redirect()->back();
        }
    }

    public function create()
    {
        try {
            $data = [];
            return view('backend.client.whatsapp.contacts.attribute.create', $data);
        } catch (Exception $e) {
            Toastr::error(__('something_went_wrong_please_try_again'));
            return redirect()->back();
        }
    }

    public function store(ContactAttributeRequest $request)
    {
        if (isDemoMode()) {
            Toastr::info(__('this_function_is_disabled_in_demo_server'));
            return redirect()->back();
        }
        return  $this->repo->store($request);
    }
 
    public function edit($id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return redirect()->back();
        }

        try {
            $contact = $this->repo->find($id);
            $data = ['contact' => $contact];
            return view('backend.client.whatsapp.contacts.attribute.edit', $data);
        } catch (Exception $e) {
            Toastr::error(__('something_went_wrong_please_try_again'));
            return redirect()->back();
        }
    }

    public function update(ContactAttributeRequest $request, $id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return redirect()->back();
        }
        return $this->repo->update($request, $id);
    }

    public function delete($id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return redirect()->back();
        }

        return $this->repo->destroy($id);
    }
}
