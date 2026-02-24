@extends('backend.layouts.master')
@section('title', __('payment_methods'))
@push('css_asset')
    <link href="{{ static_asset('admin/css/countrySelect.min.css') }}" rel="stylesheet"/>
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
        input#billing_phone {
            padding-left: 47px !important;
        }

        .iti.iti--allow-dropdown {
            width: 100%;
        }

        /* Loading effect for the submit button */
        .loading_button {
            position: relative; /* For positioning spinner */
            cursor: not-allowed !important; /* Prevent user interaction */
        }

        .loading_button::after {
            content: ""; /* Placeholder for spinner */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid #f3f3f3; /* Light gray border */
            border-top: 2px solid #354ABF; /* Spinner color */
            border-radius: 50%;
            width: 15px; /* Size of spinner */
            height: 15px;
            animation: spin 1s linear infinite; /* Animation for spinning */
        }

        @keyframes spin {
            from {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            to {
                transform: translate(-50%, -50%) rotate(360deg); /* Full spin */
            }
        }
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("{{ static_asset('admin/img/flags.png') }}");
                background-size: auto;
            }
        }

        .div_btns {
            width: 80%;
            margin: auto;
        }

        .payment_btns {
            color: #ffffff !important;
            border: none;
        }

        .payment_btns:hover {
            opacity: 0.9;
        }

    </style>
@endpush

@section('content')
    <section class="oftions">
        <div class="container-fluid">
            <form
                class="payment_form"
                method="POST"
                enctype="multipart/form-data"

                @if ($package->is_free == 1 && $package->price == 0)
                    action="{{ route('client.upgrade-plan.free', [
                        'trx_id' => $trx_id,
                        'payment_type' => 'free',
                        'package_id' => $package->id
                    ]) }}"
                @else
                    action=""
                @endif
            >
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <input type="hidden" name="plan_id" value="{{ $package->id }}">

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <h3 class="section-title">{{ __('payment_calculation') }}</h3>

                        <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30">
                            <table class="table table-borderless">
                                <tr>
                                    <td>{{ __('plan') }}</td>
                                    <td class="text-end"><h6>{{ $package->name }}</h6></td>
                                </tr>
                                <tr>
                                    <td>{{ __('price') }}</td>
                                    <td class="text-end"><h6>{{ get_price($package->price) }}</h6></td>
                                </tr>
                                <tr>
                                    <td>{{ __('total') }}</td>
                                    <td class="text-end"><h6>{{ get_price($package->price) }}</h6></td>
                                </tr>
                            </table>
                        </div>

                        <div class="mt-4 text-center">
                            @if ($package->is_free == 1 && $package->price == 0)

                                <button type="submit"
                                        class="w-100 btn sg-btn-primary mb-3">
                                    {{ __('proceed') }}
                                </button>

                            @else
                                <div class="div_btns">

                                    @if (setting('is_offline_activated'))
                                        <button style="background-color: #6c757d !important;" type="button"
                                                class="w-100 btn sg-btn-primary payment_btns mb-3 bg-offline"
                                                data-method="offline">
                                            {{ __('claim_offline_subscription') }}
                                        </button>
                                    @endif

                                    @if (setting('is_stripe_activated'))
                                        <button style="background-color: #635BFF !important" type="submit"
                                                class="w-100 btn sg-btn-primary payment_btns mb-3 bg-stripe"
                                                data-method="stripe">
                                            {{ __('proceed_stripe_payment') }}
                                        </button>
                                    @endif

                                    @if (setting('is_paypal_activated'))
                                        <button style="background-color: #003087 !important;" type="submit"
                                                class="w-100 btn sg-btn-primary payment_btns mb-3 bg-paypal"
                                                data-method="paypal">
                                            {{ __('proceed_paypal_payment') }}
                                        </button>
                                    @endif

                                    @if (setting('is_paddle_activated'))
                                        <button style="background-color: #1F1F1F !important;" type="submit"
                                                class="w-100 btn sg-btn-primary payment_btns mb-3 bg-paddle"
                                                data-method="paddle">
                                            {{ __('proceed_paddle_payment') }}
                                        </button>
                                    @endif

                                    @if (setting('is_razor_pay_activated'))
                                        <button style="background-color: #0C2451 !important;" type="submit"
                                                class="w-100 btn sg-btn-primary payment_btns mb-3 bg-razorpay"
                                                data-method="razor_pay">
                                            {{ __('proceed_razor_pay_payment') }}
                                        </button>
                                    @endif

                                    @if (setting('is_mercadopago_activated'))
                                        <button style="background-color: #009EE3 !important;" type="submit"
                                                class="w-100 btn sg-btn-primary payment_btns bg-mercadopago"
                                                data-method="mercadopago">
                                            {{ __('proceed_mercadopago_payment') }}
                                        </button>
                                    @endif

                                </div>

                            @endif
                        </div>
                    </div>
                </div>
            </form>

            <div class="row justify-content-center mt-4 mb-4 d-none" id="offline_payment">
                <div class="col-md-6">
                    <h3 class="section-title">{{ __('offline_payment_details') }}</h3>
                    <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30">
                        <form action="{{ route('client.offline.claim') }}"
                            method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $package->id }}">
                            {!! setting('offline_payment_instruction') !!}
                            <div class="d-flex justify-content-end mt-30">
                                <button type="submit" class="btn sg-btn-primary">
                                    {{ __('claim') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
@push('js')
    <script src="{{ static_asset('admin/js/countrySelect.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/intlTelInput.js') }}"></script>
    @if(setting('is_razorpay_activated'))
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif
    <script>
        $(document).ready(function () {

            $(document).on('click', '.payment_btns', function (e) {

                let method = $(this).data('method');
                let form = $('.payment_form');

                if (method === 'offline') {
                    e.preventDefault();
                    $('#offline_payment').removeClass('d-none');
                    $('html, body').animate({
                        scrollTop: $('#offline_payment').offset().top
                    }, 600);
                    return;
                }

                let url = '';

                switch (method) {
                    case 'stripe':
                        url = "{{ route('client.stripe.redirect') }}?trx_id={{ $trx_id }}&payment_type=stripe";
                        break;
                    case 'paypal':
                        url = "{{ route('client.paypal.redirect') }}?trx_id={{ $trx_id }}&payment_type=paypal";
                        break;
                    case 'paddle':
                        url = "{{ route('client.paddle.redirect') }}?trx_id={{ $trx_id }}&payment_type=paddle";
                        break;
                    case 'razor_pay':
                        url = "{{ route('client.razor.pay.redirect') }}";
                        break;
                    case 'mercadopago':
                        url = "{{ route('client.mercadopago.redirect') }}?trx_id={{ $trx_id }}&payment_type=mercadopago";
                        break;
                }

                if (method === 'razor_pay') {
                    e.preventDefault();

                    $.post(url, {
                        _token: "{{ csrf_token() }}",
                        plan_id: '{{ $package->id }}',
                        trx_id: '{{ $trx_id }}'
                    }, function (data) {
                        if (data.success) {
                            new Razorpay(data).open();
                        } else {
                            toastr.error(data.error);
                        }
                    });

                    return;
                }

                form.attr('action', url).submit();
            });
        });
    </script>


@endpush
 