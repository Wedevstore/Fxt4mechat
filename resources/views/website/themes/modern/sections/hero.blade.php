<!-- Banner Section Start -->
<section class="banner__section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="hero__text text-center" data-aos="fade-up" data-aos-duration="700">
                    <h4 class="subtitle">
                        @php
                            $small_title = $small_titles->first();
                        @endphp

                        @if ($small_title)
                            <span class="icon">
                                <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                            </span>
                            {{ @$small_title->language->title, app()->getLocale() }}
                        @endif
                        
                    </h4>
                    <h1 class="title">{!! setting('hero_title',app()->getLocale()) !!}</h1>
                    
                    <ul class="banner__list">
                        <li>{!! setting('hero_subtitle',app()->getLocale()) !!}</li>
                    </ul>
                    {{-- <p class="description">
                        {!! setting('hero_description',app()->getLocale()) !!}
                    </p> --}}
                    <!-- <p class="desc">
                        salemag, developed by SpaGreen Creative, is a WhatsApp and Telegram Marketing Software as a Service (SaaS) script available for purchase on the
                        Codecanyon marketplace.Hero Description
                    </p> -->

                    <div class="btn__group">
                        @if(setting('hero_main_action_btn_enable') == '1')
                            <a class="btn btn-primary" href="{{setting('hero_main_action_btn_url',app()->getLocale())}}">{{setting('hero_main_action_btn_label',app()->getLocale())}}</a>
                        @endif
                        @if(setting('hero_secondary_action_btn_enable') == '1')
                            <a class="btn btn-secondary" href="{{setting('hero_secondary_action_btn_url',app()->getLocale())}}">{{setting('hero_secondary_action_btn_label',app()->getLocale())}}</a>
                        @endif
                    </div>
                </div>
                <div class="banner__thumb" data-aos="fade-up" data-aos-duration="800">
                    <img src="{{  getFileLink('original_image',setting('header1_hero_image1')) }}" alt="Hero images">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Banner Section End -->