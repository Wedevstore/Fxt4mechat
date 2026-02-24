@extends('backend.layouts.master')
@section('title', __('web_settings'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="section-title">{{ __('web_settings') }}</h3>

                <div class="default-tab-list default-tab-list-v2  bg-white redious-border p-20 p-sm-30 mb-4">
                    <form action="{{ route('client.web.setting.update') }}" method="POST" enctype="multipart/form-data" class="form">
                        @csrf
                        <input type="hidden" class="is_modal" value="0" />
                        <div class="row">
                            {{-- <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>{{ __('webhook_config') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label for="webhook_callback_url" class="form-label"><i class="las la-link"></i>
                                                {{ __('callback_url') }}</label>
                                            <div class="input-group">
                                                <input type="url"
                                                    value="{{ isDemoMode() ? '******************' : route('whatsapp.web.webhook', @Auth::user()->client->webhook_verify_token) }}"
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
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="las la-key"></i> {{ __('web_settings') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4" style="display: none">
                                            <label for="rapiwa_url" class="form-label">
                                                {{ __('rapiwa_web_url') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control rounded-2" id="url"
                                                   readonly
                                                   name="url"
                                                   value="{{ isDemoMode() ? '******************' : old('url', "https://app.rapiwa.com") }}">
                                            <div class="invalid-feedback text-danger">
                                                <p class="name_error error">{{ $errors->first('url') }}</p>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="token" class="form-label">
                                                {{ __('api_key') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control rounded-2" id="client_key"
                                                   name="client_key"
                                                   value="{{ isDemoMode() ? '******************' : old('client_key', @Auth::user()->client->webSetting->access_token) }}"
                                                   placeholder="{{ __('enter_api_key') }}">
                                            <div class="invalid-feedback text-danger">
                                                <p class="client_key_error error">{{ $errors->first('client_key') }}</p>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end align-items-center mt-30 gap-2">
                                            @if (@Auth::user()->client->webSetting->access_token)
                                                <button type="button" class="btn btn-danger text-white __js_delete"
                                                    id="remove_access_token"
                                                    data-id="{{ @Auth::user()->client->webSetting->id }}"
                                                    data-url="{{ route('client.whatsappwebsettings.remove', @Auth::user()->client->webSetting->id) }}">
                                                    <i class="las la-trash-alt"></i> {{ __('remove') }}
                                                </button>
                                            @endif
                                            @if (@Auth::user()->client->webSetting)
                                                <button type="button" class="btn btn-secondary" id="sync_button"
                                                    data-id="{{ @Auth::user()->client->webSetting->id }}"
                                                    data-url="{{ route('client.web.settings.sync', @Auth::user()->client->webSetting->client_id) }}">
                                                    <i class="las la-sync-alt"></i> {{ __('sync') }}
                                                </button>
                                            @endif
                                            <button type="submit" class="btn sg-btn-primary"><i class="las la-save"></i>
                                                {{ __('save') }}</button>
                                            @include('backend.common.loading-btn', [
                                                'class' => 'btn sg-btn-primary',
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <i class="las la-key me-1"></i> {{ __('get_your_client_key') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-3 text-muted">
                                            <i class="las la-info-circle me-1"></i>
                                            {{ __('to_get_your_client_key_please_login_to') }}
                                            <a class="text-primary" href="https://app.rapiwa.com" target="_blank">
                                                {{ __('Rapiwa') }}
                                            </a>.
                                        </p>

                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="las la-check-circle text-success me-1"></i>
                                                <strong>{{ __('Step 1:') }}</strong> {{ __('navigate_to_the_api_section_in_the_sidebar.') }}
                                            </li>
                                            <li>
                                                <i class="las la-check-circle text-success me-1"></i>
                                                <strong>{{ __('Step 2:') }}</strong> {{ __('copy_your_client_key_for_integration.') }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.copy-text').click(function() {
                var inputField = $(this).closest('.input-group').find('input');
                inputField.select();
                document.execCommand("copy");
                toastr.success("{{ __('copied') }}");
            });
        });
        $(document).on('click', '.__js_delete', function() {
            confirmationAlert(
                $(this).data('url'),
                $(this).data('id'),
                'Yes, Delete It!'
            );
        });

        const confirmationAlert = (url, data_id, button_text = 'Yes, Confirmed it!') => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: button_text,
                confirmButtonColor: '#ff0000',
                preConfirm: () => {
                    // Show the loading spinner
                    Swal.showLoading();

                    return axios.post(url, {
                            data_id: data_id
                        })
                        .then(response => {
                            console.log(response);
                            // Close the loader and show the success/error message
                            Swal.fire({
                                title: response.data.message,
                                icon: response.data.status ? 'success' : 'error'
                            }).then(() => {
                                if (response.data.status) {
                                    location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            console.log(error);
                            // Show error message
                            Swal.fire('Error', error.response.data.message || 'An error occurred.',
                                'error');
                        });
                }
            });
        };

        $('#sync_button').click(function() {
            var button = $(this);
            var url = button.data('url');
            // Change cursor to progress
            $("body").css("cursor", "progress");

            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' +
                '{{ __('syncing') }}');

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    id: button.data('id')
                },
                success: function(data) {
                    // Reset cursor to default
                    $("body").css("cursor", "default");
                    button.html('<i class="las la-sync-alt"></i> {{ __('sync') }}');
                    if (data.status) {
                        toastr.success(data.message);
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Reset cursor to default
                    $("body").css("cursor", "default");
                    button.html('<i class="las la-sync-alt"></i> {{ __('sync') }}');
                    console.error('Error:', error);
                    toastr.error("{{ __('an_error_occurred_while_processing_the_request') }}");
                }
            });
        });
    </script>
@endpush
