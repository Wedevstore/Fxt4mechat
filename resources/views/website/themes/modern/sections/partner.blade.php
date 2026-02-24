<!-- Partner Section Start -->
<section class="partner__section pt-80 pb-100 py-sm-60" id="partner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="partner__wrapper">
                    <h4 class="partner__title" data-aos="fade-up" data-aos-duration="700">{!! setting('client_section_title',app()->getLocale()) !!}</h4>

                    <div class="marquee" data-aos="fade-up" data-aos-duration="800">
                        <div class="marquee__content">
                            @foreach($partner_logos as $key=>$partner_logo)
                            <div class="marquee__item"><img src="{{ getFileLink('80x80', $partner_logo->image) }}" alt="{{ $partner_logo->name }}" /></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Partner Section End -->