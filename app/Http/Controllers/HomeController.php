<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Contact;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\PageRepository;
use App\Repositories\PlanRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\WebsiteFaqRepository;
use App\Repositories\WebsiteStoryRepository;
use App\Repositories\WebsiteFeatureRepository;
use App\Repositories\WebsiteUseCaseRepository;
use App\Repositories\WebsiteNavMoreRepository;
use App\Repositories\WebsiteSmallTitleRepository;
use App\Repositories\WebsiteFlowBuilderRepository;
use App\Repositories\WebsiteHighlightedFeatureRepository;
use App\Repositories\WebsiteAdvantageRepository;
use App\Repositories\WebsitePartnerLogoRepository;
use App\Repositories\WebsiteTestimonialRepository;
use App\Repositories\WebsiteUniqueFeatureRepository;

class HomeController extends Controller
{
    protected $planRepository;

    protected $partnerLogoRepository;

    protected $storyRepository;

    protected $uniqueFeatureRepository;

    protected $featureRepository;

    protected $useCaseRepository;

    protected $navMoreRepository;

    protected $smallTitleRepository;

    protected $flowBuilderRepository;

    protected $highlightedFeatureRepository;

    protected $advantageRepository;

    protected $faqRepository;

    protected $testimonialRepository;

    public function __construct(
        PlanRepository $planRepository,
        WebsitePartnerLogoRepository $partnerLogoRepository,
        WebsiteStoryRepository $storyRepository,
        WebsiteUniqueFeatureRepository $uniqueFeatureRepository,
        WebsiteFeatureRepository $featureRepository,
        WebsiteUseCaseRepository $useCaseRepository,
        WebsiteNavMoreRepository $navMoreRepository,
        WebsiteSmallTitleRepository $smallTitleRepository,
        WebsiteFlowBuilderRepository $flowBuilderRepository,
        WebsiteHighlightedFeatureRepository $highlightedFeatureRepository,
        WebsiteAdvantageRepository $advantageRepository,
        WebsiteFaqRepository $faqRepository,
        WebsiteTestimonialRepository $testimonialRepository)
    {
        $this->planRepository          = $planRepository;
        $this->partnerLogoRepository   = $partnerLogoRepository;
        $this->storyRepository         = $storyRepository;
        $this->uniqueFeatureRepository = $uniqueFeatureRepository;
        $this->featureRepository       = $featureRepository;
        $this->useCaseRepository       = $useCaseRepository;
        $this->navMoreRepository       = $navMoreRepository;
        $this->smallTitleRepository    = $smallTitleRepository;
        $this->flowBuilderRepository   = $flowBuilderRepository;
        $this->highlightedFeatureRepository       = $highlightedFeatureRepository;
        $this->advantageRepository     = $advantageRepository;
        $this->faqRepository           = $faqRepository;
        $this->testimonialRepository   = $testimonialRepository;

    }

    public function index(Request $request, PlanRepository $planRepository)
    {

        $languages        = app('languages');
        $lang             = $request->site_lang ? $request->site_lang : App::getLocale();
        $menu_quick_link  = headerFooterMenu('footer_quick_link_menu', $lang);
        $menu_useful_link = headerFooterMenu('footer_useful_link_menu');

        $data             = [
            // 'plans'             => $this->planRepository->all(),
            'plans'             => Plan::active()->orderBy('price')->get(),
            'plans2'            => [
                'daily'       => $this->planRepository->activePlans([], 'daily'),
                'weekly'      => $this->planRepository->activePlans([], 'weekly'),
                'monthly'     => $this->planRepository->activePlans([], 'monthly'),
                'quarterly'   => $this->planRepository->activePlans([], 'quarterly'),
                'half_yearly' => $this->planRepository->activePlans([], 'half_yearly'),
                'yearly'      => $this->planRepository->activePlans([], 'yearly'),
            ],
            'partner_logos'     => $this->partnerLogoRepository->all(),
            'stories'           => $this->storyRepository->all(),
            'unique_features'   => $this->uniqueFeatureRepository->all(),
            'features'          => $this->featureRepository->all(),
            'use_cases'          => $this->useCaseRepository->all(),
            'nav_mores'          => $this->navMoreRepository->all(),
            'small_titles'          => $this->smallTitleRepository->all(),
            'flow_builders'          => $this->flowBuilderRepository->all(),
            'highlighted_features'  => $this->highlightedFeatureRepository->all(),
            'whatsapp_features' => $this->featureRepository->whatsapp(),
            'telegram_features' => $this->featureRepository->telegram(),
            'facebook_features' => $this->featureRepository->facebook(),
            'advantages'        => $this->advantageRepository->all(),
            'faqs'              => $this->faqRepository->all(),
            'testimonials'      => $this->testimonialRepository->all(),
            'menu_quick_links'  => $menu_quick_link,
            'menu_useful_links' => $menu_useful_link,
            'lang'              => $request->lang ?? app()->getLocale(),
            'menu_language'     => headerFooterMenu('header_menu', $lang),
        ];

        return view('website.themes.'.active_theme().'.home', $data);
    }

    public function page(Request $request, $link, PageRepository $pageRepository)
    {
        $page              = $pageRepository->findByLink($link);
        $lang              = $request->lang ?? app()->getLocale();
        $menu_quick_link   = headerFooterMenu('footer_quick_link_menu', $lang);
        $menu_useful_link  = headerFooterMenu('footer_useful_link_menu');
        $data['page_info'] = $pageRepository->getByLang($page->id, $lang);

        $data              = [
            'menu_quick_links'  => $menu_quick_link,
            'menu_useful_links' => $menu_useful_link,
            'lang'              => $request->lang ?? app()->getLocale(),
            'menu_language'     => headerFooterMenu('header_menu', $lang),
            'page_info'         => $pageRepository->getByLang($page->id, $lang),
        ];

        return view('website.themes.'.active_theme().'.page', $data);

        // return view('website.page', $data);
    }

    public function cacheClear()
    {
        try {
            Artisan::call('all:clear');
            Artisan::call('migrate', ['--force' => true]);
            DB::table('contact_flow_states')
            ->where('created_at', '<', Carbon::now()->subMinutes(30))
            ->delete();
            Contact::whereNotNull('deleted_at') ->forceDelete();
            Template::whereNotNull('deleted_at') ->forceDelete();
            // Check if JWT_SECRET is set
            if (empty(env('JWT_SECRET'))) {
                Artisan::call('jwt:secret');
            }
            Toastr::success(__('cache_cleared_successfully'));
            return back();
        } catch (\Exception $e) { 
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            Toastr::error('something_went_wrong_please_try_again', 'Error!');
            return back();
        }
    }

    public function changeLanguage($locale): \Illuminate\Http\RedirectResponse
    {
        cache()->get('locale');
        app()->setLocale($locale);

        return redirect()->back();
    }
}
