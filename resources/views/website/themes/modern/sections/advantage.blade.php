<!-- Advantage Section Start -->
<section class="advantage__section py-100 py-sm-60" id="advantage">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section__title-wrapper flex-item" data-aos="fade-up" data-aos-duration="700">
                    <div class="section__title">
                        <h4 class="subtitle">
                            @php
                                $small_title = $small_titles->skip(2)->first();
                            @endphp

                            @if ($small_title)
                                <span class="icon">
                                    <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                                </span>
                                {{ @$small_title->language->title, app()->getLocale() }}
                            @endif
                        </h4>
                        <h2 class="title">
                            {{ setting('advantage_section_title', app()->getLocale()) }}
                        </h2>
                    </div>
                    <div class="section__title">
                        <p class="desc">{{ setting('advantage_section_subtitle', app()->getLocale()) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($advantages as $index=>$advantage) 
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="800">
                    <div class="advantageCard">
                        <div class="icon">
                            <img src="{{ getFileLink('original_image', $advantage->image) }}" alt="{{ @$advantage->language->title}}" />
                        </div>
                        <div class="content">
                            <h4 class="title">{{ @$advantage->language->title}}</h4>
                            <p class="desc">
                                {!! @$advantage->language->description!!}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Advantage Section Start -->