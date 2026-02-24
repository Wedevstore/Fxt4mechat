<!-- FAQ Section Start -->
<section class="faq__section py-100 py-sm-60" id="faq">
    <div class="container">
        <div class="row">
            <div class="col-lg-6" data-aos="fade-up" data-aos-duration="800">
                <div class="section__title-wrapper mb-20 text-start">
                    <div class="section__title">
                        <h4 class="subtitle">
                            @php
                                $small_title = $small_titles->skip(5)->first();
                            @endphp

                            @if ($small_title)
                                <span class="icon">
                                    <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                                </span>
                                {{ @$small_title->language->title, app()->getLocale() }}
                            @endif
                        </h4>
                        <h2 class="title">{!! setting('faq_section_title',app()->getLocale()) !!}</h2>
                        <p class="desc">{!! setting('faq_section_subtitle',app()->getLocale()) !!}</p>
                    </div>
                </div>
                {{-- <div class="info__list">
                    <div class="info__title">For Any Question:</div>
                    <div class="info__item">
                        <div class="icon"><img src="{{ static_asset('website/themes/salemag/assets/images/gmail.svg')}}" alt="icon" /></div>
                        <a href="mailto:info@spagreen.net">info@spagreen.net</a>
                    </div>
                    <div class="info__item">
                        <div class="icon"><img src="{{ static_asset('website/themes/salemag/assets/images/phone.svg')}}" alt="icon" /></div>
                        <a href="tel:01400-620055">01400-620055</a>
                    </div>
                </div> --}}
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-duration="800">
                <div class="accordion__wrapper v2">
                    <ul id="faq__accordion" class="accordionjs">
                        @foreach($faqs as $key=>$faq)
                        <li class="accordion__main">
                            <div class="accordion__tab">{{ $faq->lang_question }}</div>
                            <div class="accordion__panel">
                                <p class="desc">
                                    {!! $faq->lang_answer!!}
                                </p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- FAQ Section End -->