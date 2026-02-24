(function ($) {
    "use strict";
     /*********************************
     * Table of Context
     * *******************************/

     /*********************************
    /* Sticky Navbar
    *********************************/
    $(window).scroll(function () {
        var scrolling = $(this).scrollTop();
        var stikey = $(".header");

        if (scrolling >= 50) {
            $(stikey).addClass("nav-bg");
        } else {
            $(stikey).removeClass("nav-bg");
        }
    });


    /*********************************
    /* Click Scroll Action
    ********************************/

    $(".header__menu .main__menu li a").on("click", function (e) {
        var target = this.hash,
            $target = $(target);

        $("html, body")
            .stop()
            .animate(
                {
                    scrollTop: $target.offset().top - 70,
                },
                100,
                "swing",
                function () {
                    window.location.hash = target;
                }
            );
    });

    /*********************************
    /*  Mobile Menu
    *********************************/
    $(".header__toggle").on("click", function (event) {
        // event.preventDefault();
        $(".toggle__bar").toggleClass("active");
        $(".header__menu").toggleClass("mblMenu__open");
    });

    $(".header__menu ul li").on("click", function (event) {
        // event.preventDefault();
        $(".toggle__bar").removeClass("active");
        $(".header__menu").removeClass("mblMenu__open");
    });

   /*********************************
    /*  Testimonial Slider Carousel
    *********************************/
    if ($(".testimonial__slider").length > 0) {
        var mySwiper = new Swiper ('.testimonial__slider', {
            // direction: 'vertical',
            effect: 'slide',
            slidesPerView: '1',
            spaceBetween: 30,
            centeredSlides: false,
            grabCursor: true,
            loop: true,
            autoplay: {
                enabled: false,
                delay: 2000,
                reverseDirection: true,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".testimonial-swipe-next",
                prevEl: ".testimonial-swipe-prev",
            },
            pagination: {                       //pagination(dots)
                el: '.swiper-pagination',
            },
        })
    }

    /********************************
     * Language Dropdown
     ********************************/
    $(".language__dropdown .selected").on("click", function (e) {
        e.preventDefault();
        // $(".language__dropdown .list").toggleClass("active");
        $(".list").removeClass("active");
        $(this).parents(".language__dropdown").find(".list").toggleClass("active");
    });


    $(document).on("click", function (e) {
        if ($(e.target).closest(".header__meta").length === 0 && $(e.target).closest(".language__dropdown .selected").length === 0) {
            $(".language__dropdown .list").removeClass("active");
        }
    });
    
    /**********************************
     *  Wow animation
     **********************************/
    const wow = new WOW({
        animateClass: "animated",
        offset: -100,
    });
    wow.init();


    /**********************************
     *  Back to Top JS 
     **********************************/
    $('body').append('<div id="toTop" class="back__icon"><i class="fa-solid fa-chevron-up"></i></div>');
    $(window).on('scroll', function () {
        if ($(this).scrollTop() != 0) {
            $('#toTop').addClass('active');
        } else {
            $('#toTop').removeClass('active');
        }
    });
    $('#toTop').on('click', function () {
        $("html, body").animate({ scrollTop: 0 }, 0);
        return false;
    });

    
})(jQuery);
