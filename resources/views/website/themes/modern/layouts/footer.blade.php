<!-- Footer Section Start -->
<footer class="footer__section">
    <div class="container">
        <div class="footer__inner">
            <div class="row">
                <div class="col-md-7" data-aos="fade-up" data-aos-duration="800">
                    <div class="footer__top">
                        <div class="footer__logo">
                            @php
                                $src = setting('light_logo') && @is_file_exists(setting('light_logo')['original_image']) ? get_media(setting('light_logo')['original_image']) : get_media('images/default/logo/logo-green-white.png');
                            @endphp
                            <a href="{{url('/')}}">
                                <img src="{{ $src }}" alt="logo" />
                            </a>
                        </div>
                        <div class="footer__slogun">
                            <h5 class="title">{{ setting('high_lighted_text_heading') }}</h5>
                            <p>
                                {{ setting('high_lighted_text') }}
                            </p>
                        </div>
                        @if(setting('show_payment_method_banner') == 1)
                            <div class="footer__slogun">
                                <h5 class="title">Payment Getaway</h5>
                                <div class="payment__icon">
                                    <img
                                    src="{{ setting('payment_method_banner') && @is_file_exists(setting('payment_method_banner')['original_image']) ? get_media(setting('payment_method_banner')['original_image']) : get_media('frontend/img/payment-methods/footer-payment.png') }}"
                                    alt="Payment Logos">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-5" data-aos="fade-up" data-aos-duration="800">
                    <div class="footer__wrapper">
                        <div class="footer__widget">
                            <h4 class="widget__title">Navigate Quick Link</h4>
                            <div class="widget__flex">
                                @if (setting('show_useful_link') && is_array(setting('footer_useful_link_menu')) && count(setting('footer_useful_link_menu')) > 0)
                                @php
                                    $useful_link_menu = headerFooterMenu('footer_useful_link_menu', app()->getLocale()) ? : headerFooterMenu('footer_useful_link_menu');
                                @endphp
                                    <ul class="widget__list">
                                        @foreach ($useful_link_menu as $usefulLink)
                                            <li><a href="{{ $usefulLink['url'] }}">{{ $usefulLink['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if (setting('show_quick_link') && is_array(setting('footer_quick_link_menu')) && count(setting('footer_quick_link_menu')) > 0)
                                @php
                                    $quick_link_menu = headerFooterMenu('footer_quick_link_menu', app()->getLocale()) ? : headerFooterMenu('footer_quick_link_menu');
                                @endphp
                                    <ul class="widget__list">
                                        @foreach ($quick_link_menu as $quickLink)
                                            <li><a href="{{ $quickLink['url'] }}">{{ $quickLink['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (setting('show_copyright') != 0)
        <div class="footer__bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="footer__copyright">
                            <p class="copyright text-center">
                                {{ setting('copyright_title', app()->getLocale()) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</footer>
<!-- Footer Section End -->