<!-- CtaBox Section Start -->
<section class="ctaBox__section p-0" id="cta">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="ctaBox__wrapper" data-aos="fade-up" data-aos-duration="700">
                    <div class="ctaBox__content" data-aos="fade-up" data-aos-duration="800">
                        <h4 class="subtitle">{!! setting('cta_subtitle', app()->getLocale()) !!}</h4>
                        <h2 class="title">{!! setting('cta_title', app()->getLocale()) !!}</h2>
                    </div>
                    <div class="btn__group" data-aos="fade-up" data-aos-duration="800">
                        <a href="{!! setting('cta_main_chat_btn_url', app()->getLocale()) !!}" class="btn btn-primary">{!! setting('cta_main_chat_btn_label', app()->getLocale()) !!}</a>
                        <a href="{!! setting('cta_main_action_btn_url', app()->getLocale()) !!}" class="btn btn-secondary">{!! setting('cta_main_action_btn_label', app()->getLocale()) !!}<i class="ri-arrow-right-double-fill"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- CtaBox Section End -->