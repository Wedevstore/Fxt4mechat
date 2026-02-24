<!-- Flow Builder Section Start -->
<section class="flowBuilder__section py-100 bg-color py-sm-60">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section__title-wrapper text-center" data-aos="fade-up" data-aos-duration="700">
                    <div class="section__title">
                        <h4 class="subtitle">
                            @php
                                $small_title = $small_titles->skip(3)->first();
                            @endphp

                            @if ($small_title)
                                <span class="icon">
                                    <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                                </span>
                                {{ @$small_title->language->title, app()->getLocale() }}
                            @endif
                        </h4>
                        @foreach (@$flow_builders as $index => $flow_builder)
                            <h2 class="title">{{ @$flow_builder->language->title, app()->getLocale() }}</h2>
                            @if (!is_null($flow_builder->language))
                                @foreach (@$flow_builder->language->description as $description)
                                    <p class="desc">
                                        {!! $description !!}
                                    </p>
                                @endforeach
                            @endif
                            {{-- <p class="desc">Create next-level engaging chat flow UI flow bilder</p> --}}
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="flowBuilder__wrapper" data-aos="fade-up" data-aos-duration="800">
                    <div class="flowBuilder__thumb">
                        @foreach (@$flow_builders as $index => $flow_builder)
                        <img src="{{ getFileLink('original_image', $flow_builder->image) }}" alt="flow-builder" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Flow Builder Section End -->