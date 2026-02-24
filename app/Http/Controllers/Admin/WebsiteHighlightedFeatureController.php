<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\WebsiteHighlightedFeatureDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteHighlightedFeatureRequest;
use App\Repositories\LanguageRepository;
use App\Repositories\WebsiteHighlightedFeatureRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteHighlightedFeatureController extends Controller
{
    protected $highlightedFeatureRepository;

    protected $language;

    public function __construct(WebsiteHighlightedFeatureRepository $highlightedFeatureRepository, LanguageRepository $language)
    {
        $this->highlightedFeatureRepository = $highlightedFeatureRepository;
        $this->language          = $language;

    }

    public function index(WebsiteHighlightedFeatureDataTable $dataTable)
    {
        return $dataTable->render('backend.admin.website.highlighted_feature.index');
    }

    public function create(Request $request, LanguageRepository $language): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $lang = $request->lang ?? app()->getLocale();
        $data = [
            'lang' => $lang,
        ];

        return view('backend.admin.website.highlighted_feature.create', $data);
    }

    public function store(WebsiteHighlightedFeatureRequest $request): \Illuminate\Http\JsonResponse
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
            $this->highlightedFeatureRepository->store($request);

            Toastr::success(__('create_successful'));

            DB::commit();

            return response()->json([
                'success' => __('create_successful'),
                'route'   => route('highlighted-feature.index'),
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
            $feature = $this->highlightedFeatureRepository->find($id);
            $lang    = $request->lang ?? app()->getLocale();


            $data    = [
                'lang'             => $lang,
                'feature_language' => $this->highlightedFeatureRepository->getByLang($id, $lang),
                'feature'          => $feature,
            ];

            

            return view('backend.admin.website.highlighted_feature.edit', $data);
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return back();
        }
    }

    public function update(WebsiteHighlightedFeatureRequest $request, $id): \Illuminate\Http\JsonResponse
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
            $this->highlightedFeatureRepository->update($request, $id);
            Toastr::success(__('update_successful'));
            DB::commit();

            return response()->json([
                'success' => __('update_successful'),
                'route'   => route('highlighted-feature.index'),
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
            $this->highlightedFeatureRepository->destroy($id);
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
            $this->highlightedFeatureRepository->status($request->all());
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
