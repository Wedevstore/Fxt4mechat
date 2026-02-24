<!-- Main Feature Start -->
<section class="feature__section bg-color py-100 py-sm-60" id="features">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section__title-wrapper flex-item" data-aos="fade-up" data-aos-duration="700">
                    <div class="section__title">
                        <h4 class="subtitle">
                            @php
                                $small_title = $small_titles->skip(1)->first();
                            @endphp

                            @if ($small_title)
                                <span class="icon">
                                    <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                                </span>
                                {{ @$small_title->language->title, app()->getLocale() }}
                            @endif
                        </h4>
                        <h2 class="title">{!! setting('feature_section_title', app()->getLocale()) !!}</h2>
                    </div>
                    <div class="section__title">
                        <div class="custom__tabs mb-30">
                            <ul class="nav nav-pills" id="pills-tabContent" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button
                                            class="nav-link active"
                                            id="pills-whatsApp-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#pills-whatsApp"
                                            type="button"
                                            role="tab"
                                            aria-controls="pills-whatsApp"
                                            aria-selected="true"
                                    >
                                    {{ __('whatsapp') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button
                                            class="nav-link"
                                            id="pills-telegram-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#pills-telegram"
                                            type="button"
                                            role="tab"
                                            aria-controls="pills-telegram"
                                            aria-selected="false"
                                    >
                                    {{ __('telegram') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button
                                            class="nav-link"
                                            id="pills-facebook-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#pills-facebook"
                                            type="button"
                                            role="tab"
                                            aria-controls="pills-facebook"
                                            aria-selected="false"
                                    >
                                    {{ __('facebook') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <p class="desc">
                            {!! setting('feature_section_subtitle', app()->getLocale()) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="tabContent__wrapper">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pills-whatsApp" role="tabpanel" aria-labelledby="pills-whatsApp-tab">
                            @if (!is_null($whatsapp_features))
                                @foreach (@$whatsapp_features as $index => $whatsapp_feature)
                                    @if ($index % 2 == 0)
                                        <div class="feature__inner" data-aos="fade-up" data-aos-duration="800">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$whatsapp_feature->language->title, app()->getLocale() }}</h4>

                                                    @if (!is_null($whatsapp_feature->language))
                                                        @foreach (@$whatsapp_feature->language->description as $description)
                                                            <p class="desc">
                                                                {!! $description !!}
                                                            </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $whatsapp_feature->image) }}" alt="feature-image" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="feature__inner" data-aos="fade-up" data-aos-duration="800">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$whatsapp_feature->language->title, app()->getLocale() }}</h4>

                                                    @if (!is_null($whatsapp_feature->language))
                                                        @foreach (@$whatsapp_feature->language->description as $description)
                                                            <p class="desc">
                                                                {!! $description !!}
                                                            </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $whatsapp_feature->image) }}" alt="feature" />
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="tab-pane fade" id="pills-telegram" role="tabpanel" aria-labelledby="pills-telegram-tab">
                            @if (!is_null($telegram_features))
                                @foreach ($telegram_features as $index => $telegram_feature)
                                    @if ($index % 2 == 0)
                                        <div class="feature__inner">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$telegram_feature->language->title, app()->getLocale() }}</h4>
                                                    @if (!is_null($telegram_feature->language))
                                                        @foreach (@$telegram_feature->language->description as $description)
                                                        <p class="desc">
                                                            {!! $description !!}
                                                        </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $telegram_feature->image) }}" alt="feature" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="feature__inner row-reverse">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$telegram_feature->language->title, app()->getLocale() }}</h4>
                                                    @if (!is_null($telegram_feature->language))
                                                        @foreach (@$telegram_feature->language->description as $description)
                                                        <p class="desc">
                                                            {!! $description !!}
                                                        </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $telegram_feature->image) }}" alt="feature" />
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="tab-pane fade" id="pills-facebook" role="tabpanel" aria-labelledby="pills-facebook-tab">
                            @if (!is_null($facebook_features))
                                @foreach ($facebook_features as $index => $facebook_feature)
                                    @if ($index % 2 == 0)
                                        <div class="feature__inner">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$facebook_feature->language->title, app()->getLocale() }}</h4>
                                                    @if (!is_null($facebook_feature->language))
                                                        @foreach (@$facebook_feature->language->description as $description)
                                                            <p class="desc">
                                                                {!! $description !!}
                                                            </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $facebook_feature->image) }}" alt="feature" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="feature__inner row-reverse">
                                            <div class="feature__list">
                                                <div class="heading">
                                                    <h4 class="title">{{ @$facebook_feature->language->title, app()->getLocale() }}</h4>
                                                    @if (!is_null($facebook_feature->language))
                                                        @foreach (@$facebook_feature->language->description as $description)
                                                            <p class="desc">
                                                                {!! $description !!}
                                                            </p>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="feature__thumb">
                                                <img src="{{ getFileLink('original_image', $facebook_feature->image) }}" alt="feature" />
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Main Feature End -->