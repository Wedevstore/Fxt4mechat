<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>{{ __('signup') }}</title>
    <!--====== LineAwesome ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/line-awesome.min.css') }}">
    <!--====== Dropzone CSS ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.min.css') }}">
    <!--====== Summernote CSS ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-lite.min.css') }}">
    <!--====== Choices CSS ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/choices.min.css') }}">
    <!--====== AppCSS ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/app.css') }}">
    <!--====== ResponsiveCSS ======-->
    <link rel="stylesheet" href="{{ static_asset('admin/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/countrySelect.min.css') }}"/>
    <link rel="stylesheet" href="{{ static_asset('admin/css/intlTelInput.css') }}">
    <style>
        input.country_selector,
        .country_selector button {
            height: 35px;
            margin: 0;
            padding: 6px 12px;
            border-radius: 2px;
            font-family: inherit;
            font-size: 100%;
            color: inherit;
        }
        input#country_selector,
        input#phone {
            padding-left: 47px !important;
        }
        .iti.iti--allow-dropdown {
            width: 100%;
        }
        /* Loading effect for the submit button */
        .loading_button {
            position: relative;
            cursor: not-allowed !important;
        }

        .loading_button::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid #f3f3f3;
            border-top: 2px solid #354ABF;
            border-radius: 50%;
            width: 15px;
            height: 15px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("{{ static_asset('admin/img/flags.png') }}");
                background-size: auto;
            }
        }
    </style>

</head>

<body>
    <section class="signup-section">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-lg-6 col-md-6 col-sm-8 position-relative">
                    <img src="{{ static_asset('admin/img/shape/rect.svg') }}" alt="Rect Shape" class="bg-rect-shape">
                    <img src="{{ static_asset('admin/img/shape/circle.svg') }}" alt="Rect Shape"
                        class="bg-circle-shape">
                    <img src="{{ static_asset('admin/img/shape/circle-block.svg') }}" alt="Rect Shape"
                        class="bg-circle-block-shape">
                    <div class="login-form bg-white rounded-20">
                        <div class="logo d-flex justify-content-center items-center mb-5">
                            <a href="{{ url('/') }}">
                                <img style="max-height: 35px"
                                    src="{{ setting('light_logo') && @is_file_exists(setting('light_logo')['original_image']) ? get_media(setting('light_logo')['original_image']) : getFileLink('80x80', []) }}"
                                    alt="Corporate Logo">
                            </a>
                        </div>
                        <h3>{{ __('sign_up') }}</h3>
                        @include('backend.common.flash')
                        <form method="POST" action="{{ route('signup.store') }}">
                            @csrf
                            <div class="row gx-20">
                        
                                <div class="col-lg-12">
                                    <div class="mb-30 ">
                                        <label for="first_name" class="form-label">{{ __('name') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-2" id="first_name"
                                            value="{{ old('first_name') }}" name="first_name"
                                            placeholder="{{ __('enter_name') }}" required autofocus>
                                        <x-input-error :messages="$errors->get('first_name')" class="mt-2 nk-block-des text-danger" />
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-30">
                                        <label for="email" class="form-label">{{ __('email') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control rounded-2" id="first_name"
                                            value="{{ old('email') }}" name="email"
                                            placeholder="{{ __('email') }}" required autofocus>
                                        <x-input-error :messages="$errors->get('email')" class="mt-2 nk-block-des text-danger" />
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-30">
                                        <label for="password" class="form-label">{{ __('password') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-2" id="password"
                                            placeholder="{{ __('password') }}" name="password" required autofocus>
                                        <x-input-error :messages="$errors->get('password')" class="mt-2 nk-block-des text-danger" />
                                    </div>
                                </div>
                                @if (setting('is_recaptcha_activated') && setting('recaptcha_site_key'))
                                    <div class="mb-30">
                                        <div id="html_element" class="g-recaptcha"
                                            data-sitekey="{{ setting('recaptcha_site_key') }}"></div>
                                    </div>
                                @endif
                                <div class="row justify-content-center">
                                    <div class="col-lg-6 mb-30">
                                        <div class="text-center">
                                            <button type="submit"
                                                class="btn btn-lg sg-btn-primary">{{ __('sign_up') }}</button>
                                        </div>
                                    </div>
                                    <span class="text-center d-block">{{ __('already_have_an_account') }}?
                                        <a href="{{ route('login') }}"
                                            class="sg-text-primary">{{ __('login') }}</a> | <a
                                            href="{{ url('/') }}"
                                            class="sg-text-primary">{{ __('back_to_website') }}</a>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ static_asset('admin/js/jquery.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/choices.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/summernote-lite.min.js') }}"></script>
    <!--====== ReCAPTCHA ======-->
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script src="{{ static_asset('admin/js/toastr.min.js') }}"></script>
    {!! Toastr::message() !!}
    @if (setting('is_recaptcha_activated') && setting('recaptcha_site_key'))
        <script type="text/javascript">
            var onloadCallback = function() {
                grecaptcha.render('html_element', {
                    'sitekey': '{{ setting('recaptcha_site_key') }}',
                    'size': 'md'
                });
            };
        </script>
    @endif

    <script src="{{ static_asset('admin/js/countrySelect.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/intlTelInput.js') }}"></script>
    <script>
        $(document).ready(function() {
            var countryCode = '';
            var input = document.querySelector("#phone");
            window.intlTelInput(input, {
                autoHideDialCode: false,
                autoPlaceholder: "on",
                dropdownContainer: document.body,
                formatOnDisplay: true,
                geoIpLookup: function(callback) {
                    $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                        var countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                hiddenInput: "full_number",
                initialCountry: "auto",
                nationalMode: false,
                placeholderNumberType: "MOBILE",
                preferredCountries: ['us', 'uk', 'ca'],
                separateDialCode: false,
                utilsScript: "{{ static_asset('admin/js/utils.js') }}",
            });
        })
    </script>


</body>

</html>
