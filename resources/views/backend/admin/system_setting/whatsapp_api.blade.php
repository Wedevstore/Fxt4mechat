@extends('backend.layouts.master')
@section('title', __('general_setting'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-md-center">
            <div class="col col-lg-6 col-md-9">
                <div class="section-top">
                    <h6 class="section-title">{{ __('whatsapp_api_setup') }}</h6>
                    {{-- <div class=" d-flex gap-20">
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2" data-bs-toggle="modal"
                            data-bs-target="#config_tutorial">
                            <i class="lab la-youtube" style="font-size:24px;"></i> {{ __('tutorial') }}
                        </a>
                    </div> --}}
                </div>
                <div class="bg-white redious-border pt-30 p-20 p-sm-30">

                   <div class="mb-4">
                        <label for="webhook_callback_url" class="form-label"><i class="las la-link"></i>
                            {{ __('callback_url') }}</label>
                        <div class="input-group">
                            <input type="url"
                                value="{{ isDemoMode() ? '******************' : route('webhook.whatsapp-webhook') }}"
                                readonly name="webhook_callback_url" class="form-control"
                                placeholder="{{ __('enter_webhook_callback_url') }}"
                                aria-label="{{ __('enter_webhook_callback_url') }}"
                                aria-describedby="webhook_callback_url">
                            <span class="input-group-text copy-text" id="webhook_callback_url">
                                <i class="las la-copy"></i>
                            </span>
                        </div>
                        <div class="nk-block-des text-danger">
                            <p class="first_name_error error">
                                {{ $errors->first('webhook_callback_url') }}
                            </p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="webhook_verify_token" class="form-label"><i class="las la-key"></i>
                            {{ __('verify_token') }}</label>
                        <div class="input-group">
                            <input type="text"
                                value="{{ isDemoMode() ? '******************' : old('webhook_verify_token', @setting('webhook_verify_token')) }}"
                                readonly name="webhook_verify_token" class="form-control"
                                placeholder="{{ __('enter_webhook_verify_token') }}"
                                aria-label="{{ __('enter_webhook_verify_token') }}"
                                aria-describedby="webhook_verify_token">
                            <span class="input-group-text copy-text" id="webhook_verify_token">
                                <i class="las la-copy"></i>
                            </span>
                        </div>
                        <div class="nk-block-des text-danger">
                            <p class="first_name_error error">
                                {{ $errors->first('webhook_verify_token') }}
                            </p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="privacy_policy" class="form-label"><i class="las la-link"></i>
                            {{ __('privacy_policy') }}</label>
                        <div class="input-group">
                            <input type="text" value="{{ url('page/privacy-policy') }}" readonly
                                name="privacy_policy" class="form-control"
                                placeholder="{{ __('enter_privacy_policy') }}"
                                aria-label="{{ __('enter_privacy_policy') }}"
                                aria-describedby="privacy_policy">
                            <span class="input-group-text copy-text" id="privacy_policy">
                                <i class="las la-copy"></i>
                            </span>
                        </div>
                        <div class="nk-block-des text-danger">
                            <p class="first_name_error error">
                                {{ $errors->first('privacy_policy') }}
                            </p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="terms_and_conditions" class="form-label"><i class="las la-link"></i>
                            {{ __('terms_and_conditions') }}</label>
                        <div class="input-group">
                            <input type="text" value="{{ url('page/terms-and-conditions') }}" readonly
                                name="terms_and_conditions" class="form-control"
                                placeholder="{{ __('enter_terms_and_conditions') }}"
                                aria-label="{{ __('enter_terms_and_conditions') }}"
                                aria-describedby="terms_and_conditions">
                            <span class="input-group-text copy-text" id="terms_and_conditions">
                                <i class="las la-copy"></i>
                            </span>
                        </div>
                        <div class="nk-block-des text-danger">
                            <p class="first_name_error error">
                                {{ $errors->first('terms_and_conditions') }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('general.whatsapp-api.update') }}" method="post" enctype="multipart/form-data" class="form">
                        @csrf
                        <input type="hidden" name="is_modal" class="is_modal" value="0">
                        <div class="row gx-20">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="d-flex gap-12 sandbox_mode_div mb-4">
                                        <input type="hidden" name="is_embedded_signup_active"
                                            value="{{ setting('is_embedded_signup_active') == 1 ? 1 : 0 }}">
                                        <label class="form-label"
                                            for="is_embedded_signup_active">{{ __('status') }}</label>
                                        <div class="setting-check">
                                            <input type="checkbox" value="1" id="is_embedded_signup_active"
                                                class="sandbox_mode"
                                                {{ setting('is_embedded_signup_active') == 1 ? 'checked' : '' }}>
                                            <label for="is_embedded_signup_active"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-12">
                                <div class="mb-4">
                                    <label for="meta_app_name" class="form-label">{{ __('meta_app_name') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_app_name" class="form-control rounded-2"
                                        id="companyName"
                                        value="{{ old('meta_app_name', setting('meta_app_name')) }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="meta_app_name_error error">{{ $errors->first('meta_app_name') }}
                                        </p>
                                    </div>
                                </div>
                            </div>   
                            <div class="col-12 col-lg-12">
                                <div class="mb-4">
                                    <label for="meta_app_id" class="form-label">{{ __('meta_app_id') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_app_id" class="form-control rounded-2"
                                        id="meta_app_id"
                                        value="{{ old('meta_app_id', setting('meta_app_id')) }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="meta_app_id_error error">{{ $errors->first('meta_app_id') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="mb-4">
                                    <label for="meta_configuration_id"
                                        class="form-label">{{ __('meta_configuration_id') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_configuration_id" class="form-control rounded-2"
                                        id="meta_configuration_id"
                                        value="{{ old('meta_configuration_id', setting('meta_configuration_id')) }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="meta_configuration_id_error error">
                                            {{ $errors->first('meta_configuration_id') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-12">
                                <div class="mb-4">
                                    <label for="meta_app_secret"
                                        class="form-label">{{ __('meta_app_secret') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_app_secret" class="form-control rounded-2"
                                        id="meta_app_secret"
                                        value="{{ old('meta_app_secret', setting('meta_app_secret')) }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="meta_app_secret_error error">
                                            {{ $errors->first('meta_app_secret') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-12">
                                <div class="mb-4">
                                    <label for="meta_access_token"
                                        class="form-label">{{ __('meta_access_token') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_access_token" class="form-control rounded-2"
                                        id="meta_access_token"
                                        value="{{ old('meta_access_token', setting('meta_access_token')) }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="meta_access_token_error error">
                                            {{ $errors->first('meta_access_token') }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row gx-20">
                            <div class="d-flex justify-content-end align-items-center mt-30">
                                <button type="submit" class="btn sg-btn-primary">{{ __('submit') }}</button>
                                @include('backend.common.loading-btn', ['class' => 'btn sg-btn-primary'])
                            </div>
                        </div>
                    </form>
                    {{-- <span class="text-center d-block">
                        <a href="https://developers.facebook.com/docs/whatsapp/embedded-signup/embed-the-flow" target="_blank" class="sg-text-primary">
                            {{ __('learn_more') }}
                        </a>
                    </span> --}}
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade modal-lg" id="config_tutorial" tabindex="-1" aria-labelledby="config_tutorial"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="embed-responsive videowrapper">
                    <iframe class="embed-responsive-item"
                        src="https://www.youtube.com/embed/"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
     $(document).ready(function() {
            $('.copy-text').click(function() {
                var inputField = $(this).closest('.input-group').find('input');
                inputField.select();
                document.execCommand("copy");
                toastr.success("{{ __('copied') }}");
            });
        });
</script>
@endpush
