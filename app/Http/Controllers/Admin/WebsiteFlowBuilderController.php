<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\WebsiteFlowBuilderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteFlowBuilderRequest;
use App\Repositories\LanguageRepository;
use App\Repositories\WebsiteFlowBuilderRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteFlowBuilderController extends Controller
{
    protected $flowBuilderRepository;

    protected $language;

    public function __construct(WebsiteFlowBuilderRepository $flowBuilderRepository, LanguageRepository $language)
    {
        $this->flowBuilderRepository = $flowBuilderRepository;
        $this->language          = $language;

    }

    public function index(WebsiteFlowBuilderDataTable $dataTable)
    {
        return $dataTable->render('backend.admin.website.flow_builder.index');
    }

    public function create(Request $request, LanguageRepository $language): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $lang = $request->lang ?? app()->getLocale();
        $data = [
            'lang' => $lang,
        ];

        return view('backend.admin.website.flow_builder.create', $data);
    }

    public function store(WebsiteFlowBuilderRequest $request): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            $this->flowBuilderRepository->store($request);

            Toastr::success(__('create_successful'));

            DB::commit();

            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('flow-builder.index'),
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();

            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function edit($id, Request $request, LanguageRepository $language): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        try {
            $feature = $this->flowBuilderRepository->find($id);
            $lang    = $request->lang ?? app()->getLocale();


            $data    = [
                'lang'             => $lang,
                'feature_language' => $this->flowBuilderRepository->getByLang($id, $lang),
                'feature'          => $feature,
            ];

            

            return view('backend.admin.website.flow_builder.edit', $data);
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return back();
        }
    }

    public function update(WebsiteFlowBuilderRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            $this->flowBuilderRepository->update($request, $id);
            Toastr::success(__('update_successful'));
            DB::commit();

            return response()->json([
                'success' => __('update_successful'),
                'route'   => route('flow-builder.index'),
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());

            DB::rollBack();

            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
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
            $this->flowBuilderRepository->destroy($id);
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
            $this->flowBuilderRepository->status($request->all());
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
}
