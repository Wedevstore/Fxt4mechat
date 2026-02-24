(function ($) {
    "use strict";
     /*********************************
     * Table of Context
     * *******************************/

    /*********************************
    /* Preloader Start
    *********************************/
    $(window).on('load', function() {
        $('#salebot__preloader').addClass('loaded');
        setTimeout(function() {
            $('#preloader').remove();
        }, 1000); // 1 second delay to remove preloader
    });


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
    /*  Mobile Menu Flyout Menu
    *********************************/
    $(".main__menu .has__dropdown").on("click", function (event) {
        event.preventDefault();
        
        // Remove 'active' class from all other '.has__dropdown' elements
        $(".main__menu .has__dropdown").not(this).removeClass("active");
        
        // Toggle 'active' class on the clicked element
        $(this).toggleClass("active");
    });

    // Remove 'active' class when clicking outside
    $(document).on("click", function (event) {
        // Check if the click is outside '.main__menu .has__dropdown'
        if (!$(event.target).closest(".main__menu .has__dropdown").length) {
            $(".main__menu .has__dropdown").removeClass("active");
        }
    });

    

    /*********************************
    /*  Mobile Menu Flyout Menu
    *********************************/
    $(".header__toggle .toggle__bar").on("click", function (event) {
        event.preventDefault();
        $(".header__toggle").addClass("active");
        $(".flyoutMenu").toggleClass("active");
    });
    $(".closest__btn").on("click", function (event) {
        event.preventDefault();
        $(".header__toggle").removeClass("active");
        $(".flyoutMenu").toggleClass("active");
    });

    $(document).on("click", function (e) {
        if ($(e.target).closest(".flyout__flip").length === 0 && $(e.target).closest(".header__toggle .toggle__bar").length === 0) {
            $(".header__toggle, .flyoutMenu").removeClass("active");
        }
    });

    /*********************************
    /*  Mobile Menu Expand
    *********************************/
    $(".flyout-main__menu .has__dropdown .nav__link").click(function() {
        $(".sub__menu").slideUp(400);
        if (
          $(this)
            .parent()
            .hasClass("active")
        ) {
          $(".has__dropdown").removeClass("active");
          $(this)
            .parent()
            .removeClass("active");
        } else {
          $(".has__dropdown").removeClass("active");
          $(this)
            .next(".sub__menu")
            .slideDown(400);
          $(this)
            .parent()
            .addClass("active");
        }
      });


    /*********************************
    /* Click Scroll Action
    ********************************/

    $(".header__menu ul li a").on("click", function (e) {
        var target = this.hash,
            $target = $(target);

        $("html, body")
            .stop()
            .animate(
                {
                    scrollTop: $target.offset().top - 30,
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
    // $(".header__toggle").on("click", function (event) {
    //     event.preventDefault();
    //     $(".toggle__bar").toggleClass("active");
    //     $(".header__menu").toggleClass("mblMenu__open");
    // });

    // $(".header__menu ul li").on("click", function (event) {
    //     event.preventDefault();
    //     $(".toggle__bar").removeClass("active");
    //     $(".header__menu").removeClass("mblMenu__open");
    // });


    /********************************
    * Language Dropdown
    ********************************/
    $(".language__dropdown .selected").on("click", function (e) {
        e.preventDefault();
        $(".dropdown__list").toggleClass("active");
        // $(".dropdown__list").removeClass("active");
        // $(this).parents(".meta__list").find(".dropdown__list").addClass("active");
    });

    $(document).on("click", function (e) {
        if ($(e.target).closest(".meta__list").length === 0 && $(e.target).closest(".language__dropdown").length === 0) {
            $(".dropdown__list").removeClass("active");
        }
    });


    /********************************
    * Service Toggle Class
    ********************************/
    $(".pricing__item").hover(
        function() {
            // On mouse enter
            $(".pricing__item").removeClass("active");
            $(this).addClass("active");
        }, 
    );
    


     /*********************************
    /*  Story Slider
    *********************************/
    if ($(".story__slider").length > 0) {
        var storySlider = new Swiper(".story__slider", {
            loop: false,
            spaceBetween: 24,
            grabCursor: true,
            autoplay: {
                enabled: false,
                delay: 2000,
            },
            navigation: {
                nextEl: ".story-swipe-next",
                prevEl: ".story-swipe-prev",
            },
            breakpoints: {
                300: {
                    slidesPerView: 1,
                },
                400: {
                    slidesPerView: 1,
                },
                479: {
                    slidesPerView: 1,
                },
                575: {
                    slidesPerView: 2,
                },
                767: {
                    slidesPerView: 2,
                },
                991: {
                    slidesPerView: 3,
                },
                1400: {
                    slidesPerView: 3,
                },
            },
        });
    }

    /*********************************
    /*  Accordion
    *********************************/
    if ($("#faq__accordion, #feature__accordion").length > 0) {
        $("#faq__accordion, #feature__accordion").accordionjs({
            // Allow self close.(data-close-able)
            closeAble   : true,
            // Close other sections.(data-close-other)
            closeOther  : true,
            // Animation Speed.(data-slide-speed)
            slideSpeed  : 250,
            // The section open on first init. A number from 1 to X or false.(data-active-index)
            activeIndex : 1,
        });
    }

    /**********************************
    // Price Range Slider
    /**********************************/


    if ($("#slider-range1").length > 0) {
        $('#slider-range1').alRangeSlider({
            allowSmoothTransition: true,
            showInputs: false,
            selectedPoints: [10, 50] // Example values for slider 1
        });
    }
    
    if ($("#slider-range2").length > 0) {
        $('#slider-range2').alRangeSlider({
            allowSmoothTransition: true,
            showInputs: false,
            selectedPoints: [20, 60] // Example values for slider 2
        });
    }
    
    if ($("#slider-range3").length > 0) {
        $('#slider-range3').alRangeSlider({
            allowSmoothTransition: true,
            showInputs: false,
            selectedPoints: [30, 70] // Example values for slider 3
        });
    }
    
    if ($("#slider-range4").length > 0) {
        $('#slider-range4').alRangeSlider({
            allowSmoothTransition: true,
            showInputs: false,
            selectedPoints: [40, 80] // Example values for slider 4
        });
    }
    



    // if ($("#slider-range").length > 0) {
    //     $("#slider-range").slider({
    //         range: true,
    //         min: 0,
    //         max: 100,
    //         values: [13, 80],
    //         slide: function (event, ui) {
    //             // $("#minamount").html("$" + ui.values[0]);
    //             $("#amount").html("$" + ui.values[1]);
    //         }
    //     });
    //     // $("#minamount").html("$" + $("#slider-range").slider("values", 0));
    //     $("#amount").html("$" + $("#slider-range").slider("values", 1));

    // };

    
    // if ($("#slider-range").length > 0) {
    //     $("#slider-range").slider({
    //         range: true,  // Set to false for a single slider handle
    //         min: 0,
    //         max: 100,
    //         value: 50,  // Initial position of the single handle
    //         slide: function (event, ui) {
    //             $("#amount").html("$" + ui.value);
    //         }
    //     });
    //     $("#amount").html("$" + $("#slider-range").slider("value"));
    // }

    // Initialize sliders
    // createSlider("#slider-range1", "#tooltip1", 0, 5000, 1205);
    // createSlider("#slider-range2", "#tooltip2", 0, 3000, 500);
    // createSlider("#slider-range3", "#tooltip3", 0, 2000, 1000);

    // Function to create a slider with tooltip
    // function createSlider(sliderId, tooltipId, min, max, initialValue) {
    //     $(sliderId).slider({
    //         min: min,
    //         max: max,
    //         value: initialValue,
    //         create: function() {
    //             $(tooltipId).html($(this).slider("value"));
    //             updateTooltipPosition(sliderId, tooltipId);
    //         },
    //         slide: function(event, ui) {
    //             $(tooltipId).html(ui.value);
    //             updateTooltipPosition(sliderId, tooltipId);
    //         }
    //     });
    // }

    // Function to update tooltip position based on slider handle
    // function updateTooltipPosition(sliderId, tooltipId) {
    //     var position = $(sliderId + " .ui-slider-handle").position().left;
    //     $(tooltipId).css("left", position + "px");
    // }



    /**********************************
    /*  AOS animation
    **********************************/
    AOS.init();

    /**********************************
     *  Back to Top JS 
     **********************************/
    $('body').append('<div id="toTop" class="back__icon"><i class="ri-arrow-up-double-line"></i></div>');
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 300) { 
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