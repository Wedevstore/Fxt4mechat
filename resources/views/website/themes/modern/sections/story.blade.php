<!-- Success Story Start -->
<section class="success__story" id="stories">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="story__wrapper" data-aos="fade-up" data-aos-duration="600">
                    <div class="section__title-wrapper text-center">
                        <div class="section__title text-white">
                            <h2 class="title" data-aos="fade-up" data-aos-duration="800">{!! setting('story_section_title',app()->getLocale()) !!}</h2>
                            <p class="desc" data-aos="fade-up" data-aos-duration="900">
                                {!! setting('story_section_subtitle',app()->getLocale()) !!}
                            </p>
                        </div>
                    </div>

                    <div class="story__inner" data-aos="fade-up" data-aos-duration="1000">
                        <div class="swiper story__slider">
                            <div class="swiper-wrapper">
                                @foreach($stories as $key=>$story)
                                    <div class="swiper-slide">
                                        <!-- StoryCard -->
                                        <div class="storyCard">
                                            <div class="storyCard__header">
                                                <div class="icon"><img src="{{ getFileLink('original_image',  $story->image) }}" alt="story" /></div>
                                                <h5 class="title"></h5>
                                            </div>
                                            <div class="storyCard__content">
                                                <p>{!! @$story->language->description !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Swiper Navigation -->
                        <div class="swiper__navigation">
                            <div class="story-swipe-prev swiper-button-prev">
                                <i class="ri-arrow-left-s-line"></i>
                            </div>
                            <div class="story-swipe-next swiper-button-next">
                                <i class="ri-arrow-right-s-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Success Story End -->