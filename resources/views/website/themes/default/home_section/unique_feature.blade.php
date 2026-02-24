<div class="dreamd-split-accordion-area bg-white dreamd-section-gap-big pt--0" id="unique_feature">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center sal-animate" data-sal="slide-up" data-sal-duration="700"
                    data-sal-delay="100">
                    <h3 class="title mb--0">{!! setting('unique_feature_section_title', app()->getLocale()) !!}</h3>
                </div>
            </div>
        </div>
        <div class="row row--40 mt_dec--30 split-accordion-wrapper">
            <div class="col-lg-5 col-md-12 col-sm-12 col-12 mt--30 sal-animate" data-sal="slide-up"
                data-sal-duration="700" data-sal-delay="100">
                <div class="spa-accordion-style ">
                    <div class="accordion" id="uniqueFeaturesAccordion">
                        @foreach ($unique_features as $key => $unique_feature)
                            @if ($unique_feature->language)
                                <div class="accordion-item card">
                                    <h2 class="accordion-header card-header" id="heading{{ $key }}"
                                        style="font-family: Inter, Bangla883, sans-serif;">
                                        <button class="accordion-button {{ $key == 0 ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $key }}"
                                            aria-expanded="{{ $key == 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $key }}">
                                            <img src="{{ getFileLink('80x80', $unique_feature->icon) }}"
                                                alt="{{ @$unique_feature->language->title, app()->getLocale() }}"
                                                style="height: 24px; width:24px;">
                                            {{ @$unique_feature->language->title }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $key }}"
                                        class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}"
                                        aria-labelledby="heading{{ $key }}"
                                        data-bs-parent="#uniqueFeaturesAccordion">
                                        <div class="accordion-body card-body">
                                            {!! @$unique_feature->language->description !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-12 col-sm-12 col-12 mt--30 sal-animate" data-sal="slide-up"
                data-sal-duration="700" data-sal-delay="200">
                <div class="split-image">
                    <img src="{{ getFileLink('714x300', setting('unique_feature_image')) }}"
                        alt="{!! setting('unique_feature_section_title', app()->getLocale()) !!}">
                </div>
            </div>
        </div>
    </div>
</div>
