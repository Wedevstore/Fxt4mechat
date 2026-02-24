<!-- Unique Feature Start -->
<section class="unique__feature py-100 py-sm-60" id="feature">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-up" data-aos-duration="700">
                {{-- <div class="section__title-wrapper mb-15">
                    <div class="section__title">
                        <h2 class="title">Our Unique Features</h2>
                        <p class="desc">salemag, developed by SpaGreen Creative, is a WhatsApp and Telegram Marketing Software as a Service (SaaS)</p>
                    </div>
                </div>
                <div class="feature__list">
                    <div class="feature__item">
                        <p>Users can access the software from any device with an internet connection.</p>
                    </div>
                    <div class="feature__item">
                        <p>SaaS products generally operate on a subscription basis, with various pricing tiers based on features, usage, or the number of users.</p>
                    </div>
                    <div class="feature__item">
                        <p>The software is regularly updated by the provider, ensuring that users always have access to the latest features</p>
                    </div>
                </div>
                <div class="btn__group mt-25">
                    <a href="#" class="btn btn-primary">Get Started</a>
                </div> --}}
                <div class="split-image">
                    <img src="{{ getFileLink('original_image', setting('unique_feature_image')) }}"
                        alt="{!! setting('unique_feature_section_title', app()->getLocale()) !!}">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="accordion__wrapper" data-aos="fade-up" data-aos-duration="700">
                    <ul id="feature__accordion" class="accordionjs">
                        
                        @foreach ($unique_features as $key => $unique_feature)
                            @if ($unique_feature->language)
                                <li class="accordion__main">
                                    <div class="accordion__tab">
                                        <div class="icon">
                                            <img src="{{ getFileLink('80x80', $unique_feature->icon) }}"
                                            alt="{{ @$unique_feature->language->title, app()->getLocale() }}"
                                            style="height: 30px; width:30px;">
                                        </div>
                                        {{ @$unique_feature->language->title }}
                                    </div>
                                    <div class="accordion__panel">
                                        <p class="desc">{!! @$unique_feature->language->description !!}</p>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Unique Feature End -->