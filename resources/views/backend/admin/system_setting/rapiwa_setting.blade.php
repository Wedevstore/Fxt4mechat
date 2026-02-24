@extends('backend.layouts.master')
@section('title', __('whatsapp_web_setting'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-md-center">
            <div class="col col-lg-6 col-md-9">
                <div class="section-top">
                    <h6 class="section-title">{{ __('whatsapp_web_setting') }}</h6>
                </div>
                <div class="bg-white redious-border pt-30 p-20 p-sm-30">
                    <form action="{{ route('rapiwa.setting.store') }}" method="post" class="form">@csrf
                        <div class="row gx-20">
                            <div class="col-lg-12 whatsapp_web_access_div">
                                <div class="mb-4">
                                    <label for="whatsapp_web_access" class="form-label">{{ __('use_credentials_whatsapp_web') }}</label>
                                    <div class="select-type-v2">
                                        <select
                                            class="form-select form-select-lg mb-3 without_search"
                                            name="whatsapp_web_access"
                                            id="whatsapp_web_access"
                                        >
                                            <option value="client" {{ setting('whatsapp_web_access') == 'client' ? 'selected' : '' }}>
                                                {{ __('client') }}
                                            </option>
                                            <option value="admin" {{ setting('whatsapp_web_access') == 'admin' ? 'selected' : '' }}>
                                                {{ __('admin') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12" id="rapiwa_api_wrapper" style="display:none">
                                <div class="mb-4">
                                    <label for="rapiwa_api" class="form-label">{{ __('rapiwa_api') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-2" id="rapiwa_api"
                                        name="admin_rapiwa_api" placeholder="{{ __('enter_rapiwa_api') }}"
                                        value="{{ isDemoMode() ? '******************' : setting('admin_rapiwa_api') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="rapiwa_api_error error"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-30">
                            <button type="submit" class="btn sg-btn-primary">{{ __('submit') }}</button>
                            @include('backend.common.loading-btn', ['class' => 'btn sg-btn-primary'])
                        </div>
                    </form>
                    <span class="text-center d-block">{{ __("if_doesn't_have_a_rapiwa_account_yet?") }} <a
                            href="https://rapiwa.com/" target="_blank"
                            class="sg-text-primary">{{ __('click_here') }}</a></span>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function () {
                const $defaultCache = $('#whatsapp_web_access');
                const $rapiwaWrapper = $('#rapiwa_api_wrapper');
        
                function toggleRapiwaWrapper() {
                    if ($defaultCache.val() === 'admin') {
                        $rapiwaWrapper.show();
                    } else {
                        $rapiwaWrapper.hide();
                    }
                }
        
                $defaultCache.on('change', function () {
                    toggleRapiwaWrapper();
                });
        
                toggleRapiwaWrapper();
            });
        </script>
    @endpush
@endsection

