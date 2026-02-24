<!-- Rewrite Section Start -->
<section class="rewrite__section py-100 py-sm-60">
    <div class="container">
        <div class="row">
            <div class="col-12">
                @if (!empty($highlighted_features) && is_iterable($highlighted_features))
                    @foreach ($highlighted_features as $index => $highlighted_features)
                        @if ($index % 2 == 0)
                            <div class="feature__inner" data-aos="fade-up" data-aos-duration="700">
                                <div class="feature__list">
                                    <div class="section__title-wrapper mb-15">
                                        <div class="section__title">
                                            <h4 class="subtitle">
                                                <span class="icon"><img src="{{ getFileLink('original_image', $highlighted_features->logo) }}" alt="icon" /></span>
                                                {{ @$highlighted_features->language->mini_title, app()->getLocale() }}
                                            </h4>
                                            <h2 class="title">{{ @$highlighted_features->language->title, app()->getLocale() }}</h2>
                                            @if (!is_null($highlighted_features->language))
                                                @foreach (@$highlighted_features->language->description as $description)
                                                    <p class="desc">
                                                        {!! $description !!}
                                                    </p>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="btn__group mt-25">
                                        <a href="{{ $highlighted_features->link }}" class="btn btn-primary">{{ @$highlighted_features->language->lable, app()->getLocale() }}</a>
                                    </div>
                                </div>
                                <div class="feature__thumb">
                                    <img src="{{ getFileLink('original_image', $highlighted_features->image) }}" alt="feature" />
                                </div>
                            </div>
                        @else
                            <div class="feature__inner" data-aos="fade-up" data-aos-duration="700">
                                <div class="feature__list">
                                    <div class="section__title-wrapper mb-15">
                                        <div class="section__title">
                                            <h4 class="subtitle">
                                                <span class="icon"><img src="{{ getFileLink('original_image', $highlighted_features->logo) }}" alt="icon" /></span>
                                                {{ @$highlighted_features->language->mini_title, app()->getLocale() }}
                                            </h4>
                                            <h2 class="title">{{ @$highlighted_features->language->title, app()->getLocale() }}</h2>
                                            @if (!is_null($highlighted_features->language))
                                                @foreach (@$highlighted_features->language->description as $description)
                                                    <p class="desc">
                                                        {!! $description !!}
                                                    </p>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="btn__group mt-25">
                                        <a href="{{ $highlighted_features->link }}" class="btn btn-primary">{{ @$highlighted_features->language->lable, app()->getLocale() }}</a>
                                    </div>
                                </div>
                                <div class="feature__thumb">
                                    <img src="{{ getFileLink('original_image', $highlighted_features->image) }}" alt="feature" />
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>
<!-- Rewrite Section End -->