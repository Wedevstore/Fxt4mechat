@extends('backend.layouts.master')
@section('title', __('whatsApp_settings'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="section-title">{{ __('unlimited_messaging_with_rapiwa') }}</h3>
                <div class="default-tab-list default-tab-list-v2  bg-white redious-border p-20 p-sm-30 mb-4">
                    <form action="{{ route('client.rapiwa.settings.update') }}" method="POST"
                        enctype="multipart/form-data" class="form">
                        @csrf
                        <input type="hidden" class="is_modal" value="0" />

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="las la-key"></i> {{ __('rapiwa_api_key') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label for="access_token" class="form-label">
                                                {{ __('api_key') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-2" id="access_token"
                                                name="access_token"
                                                value="{{ isDemoMode() ? '******************' : old('access_token', @Auth::user()->client->rapiwaSetting->access_token) }}"
                                                placeholder="{{ __('enter_rapiwa_api_key') }}">
                                            <div class="invalid-feedback nk-block-des text-danger">
                                                <p class="access_token_error error">{{ $errors->first('access_token') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="status" class="form-label">{{ __('status') }}</label>
                                            <select class="form-select form-select-lg mb-3 with_search" name="status" id="status">
                                                <option value="1"
                                                {{ old('status', @Auth::user()->client->rapiwaSetting->status ?? 1) == 1 ? 'selected' : '' }}>
                                                {{ __('active') }}
                                                </option>
                                                <option value="0"
                                                {{ old('status', @Auth::user()->client->rapiwaSetting->status ?? 1) == 0 ? 'selected' : '' }}>
                                                {{ __('inactive') }}
                                                </option>
                                            </select>

                                            <div class="nk-block-des text-danger">
                                                <p class="timezone error">{{ $errors->first('timezone') }}</p>
                                            </div>
                                        </div>

                                        
                                        <div class="d-flex justify-content-end align-items-center mt-30 gap-2 mb-30">
                                            @if (@Auth::user()->client->rapiwaSetting->access_token)
                                                <button type="button" class="btn btn-danger text-white __js_delete"
                                                    id="remove_access_token"
                                                    data-id="{{ @Auth::user()->client->rapiwaSetting->id }}"
                                                    data-url="{{ route('client.rapiwaSettings.remove-token', @Auth::user()->client->rapiwaSetting->id) }}">
                                                    <i class="las la-trash-alt"></i> {{ __('remove') }}
                                                </button>
                                            @endif
                                            <button type="submit" class="btn sg-btn-primary"><i class="las la-save"></i>
                                                {{ __('save') }}</button>
                                            @include('backend.common.loading-btn', [
                                                'class' => 'btn sg-btn-primary',
                                            ])
                                        </div>
                                        <hr>
                                        <div class="mt-4">
                                            <div class="">
                                                <img id="wa-avatar" src="" style="max-width: 60px; display: none;" class="rounded" alt="Avatar" />
                                            </div>
                                            <p id="wa-connected-btn" class="mb-3 mt-1" disabled>Checking...</p>

                                            <div class="d-flex gap-2 justify-content-between">
                                                <!-- Logout Button -->
                                                <div id="wa-logout-container" style="display: none;">
                                                    <button id="wa-logout-btn" class="btn btn-dark btn-sm px-3">
                                                        Logout
                                                    </button>
                                                </div>
                                                {{-- <button class="btn btn-dark">🔗 Connect</button> --}}
                                                <a href="#" id="checkStatusBtn" class="btn btn-outline-secondary">Check Status</a>                    
                                            </div>
                                        </div>

                                        <div class="mb-4" id="scanDiv">
                                            <div class="h-100" style="margin: auto; ">
                                                <div class="">
                                                    <div id="wa-qr-container" class="mb-4">
                                                        <p class="text-muted">⏳ Checking WhatsApp connection...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                              <div class="card h-100">
                                <div class="card-header bg-light">
                                  <h5 class="mb-0">
                                    <i class="las la-key text-primary"></i> {{ __('how_to_get_rapiwa_api_key') }}
                                  </h5>
                                </div>
                                <div class="card-body">
                                  <ul class="list-unstyled mb-4">
                                    <li class="mb-2">
                                      <i class="las la-user-plus text-success"></i>
                                      <strong>{{ __('step_1_create_account') }}</strong> —
                                      <a href="https://app.rapiwa.com/login" target="_blank">app.rapiwa.com/login</a>.<br>
                                      {{ __('enter_your_whatsapp_number_to_register') }} <em>({{ __('registration_page') }})</em>
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-check-circle text-success"></i>
                                      <strong>{{ __('step_2_verify_with_otp') }}</strong> —<br>
                                      {{ __('check_your_whatsapp_for_the_OTP_sent_by_rapiwa') }}<br>
                                      {{ __('enter_the_OTP_in_the_verification_field_to_continue') }} <em>({{ __('otp_verification') }})</em>
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-credit-card text-primary"></i>
                                      <strong>{{ __('step_3_subscribe_to_a_plan') }}</strong> —<br>
                                      {{ __('choose_and_activate_the_subscription_plan_that_best_fits_your_needs') }}
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-desktop text-secondary"></i>
                                      <strong>{{ __('step_4_open_devices_page') }}</strong> —<br>
                                      {{ __('from_your_dashboard_navigate_to_the_devices_section') }}
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-plug text-success"></i>
                                      <strong>{{ __('step_5_add_a_device') }}</strong> —<br>
                                      {{ __('click_add_device_to_connect_your_whatsapp_device_with_rapiwa') }}
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-cogs text-warning"></i>
                                      <strong>{{ __('step_6_manage_your_device') }}</strong> —<br>
                                      {{ __('once_added_click_manage_next_to_your_device') }} <em>({{ __('device_management_page') }})</em>
                                    </li>
                                    <li class="mb-2">
                                      <i class="las la-key text-danger"></i>
                                      <strong>{{ __('step_7_retrieve_your_api_key') }}</strong> —<br>
                                      {{ __('on_the_device_management_screen_you_will_see_your_api_key_copy_and_store_it_securely') }}
                                    </li>
                                    <li>
                                      <i class="las la-info-circle text-info"></i>
                                      <strong>{{ __('need_help') }}</strong> —
                                      <a class="lern-more" href="https://docs.rapiwa.com/api/api-key.html" target="_blank">Rapiwa API Docs</a>
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
    <div class="modal fade modal-lg" id="config_tutorial" tabindex="-1" aria-labelledby="config_tutorial"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="embed-responsive videowrapper">
                    <iframe class="embed-responsive-item"
                        src="https://www.youtube.com/embed/{{ getYoutubeVideoId(setting('whatsApp_settings_video_url')) }}"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
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
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>

<script>
    const apiKey = '{{ @Auth::user()->client->rapiwaSetting->access_token }}';
    const baseUrl = '{{ env('WAS_URL', 'http://localhost:3000') }}';

    async function loadWhatsAppQR() {
        const container       = document.getElementById('wa-qr-container');
        const logoutContainer = document.getElementById('wa-logout-container');
        const scanDiv          = document.getElementById('scanDiv');

        if (!container) {
            console.warn('⚠️ QR container not found (probably because session is connected)');
            return;
        }

        try { 
            const response = await fetch(`${baseUrl}/api/qr`, {
                headers: {
                    'Authorization': `Bearer ${apiKey}`
                }
            });

            const html = await response.text();
            container.innerHTML = html;

            console.log("check>>>>>>>>>>>>>", container.innerHTML);

            if (html.includes('Already connected') || html.includes('✅ Session')) {
                if (logoutContainer) logoutContainer.style.display = 'block';
                scanDiv.style.display = 'none';
            } else {
                if (logoutContainer) logoutContainer.style.display = 'none';
                scanDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('❌ Failed to fetch QR:', error);
            if (container) container.innerHTML = '<p class="text-danger">❌ Failed to load QR code.</p>';
            if (logoutContainer) logoutContainer.style.display = 'none';
        }
    }


    async function loadWhatsAppSession() {
        const logoutContainer = document.getElementById('wa-logout-container');
        const avatarImg = document.getElementById('wa-avatar');
        const connectBtn = document.getElementById('wa-connected-btn');

        try {
            const res = await fetch(`${baseUrl}/api/info`, {
                headers: {
                    'Authorization': `Bearer ${apiKey}`
                }
            });
            const data = await res.json();

            if (data.connected) {
                if (connectBtn) {
                    connectBtn.textContent = '✅ Connected';
                }

                if (data.profilePic && avatarImg) {
                    avatarImg.src = data.profilePic;
                    avatarImg.style.display = 'block';
                }

                if (logoutContainer) logoutContainer.style.display = 'block';
            } else {
                if (connectBtn) {
                    connectBtn.textContent = 'Not connected';
                }

                if (avatarImg) avatarImg.style.display = 'none';
                if (logoutContainer) logoutContainer.style.display = 'none';
            }
        } catch (e) {
            console.error('❌ Session info fetch error:', e);
        }
    }

    async function logoutWhatsAppSession() {
        try {
            const res = await fetch(`${baseUrl}/api/logout`, {
                method: 'POST',
                headers: {
                    "Authorization": `Bearer ${apiKey}`,
                    "Content-Type": "application/json"
                }
            });

            const data = await res.json();

            if (res.ok) {
                alert('✅ Session logged out');
                await loadWhatsAppQR();
                await loadWhatsAppSession();
                location.reload();
            } else {
                console.error('❌ Logout failed:', data);
                alert(`❌ Logout failed: ${data?.error || 'Unknown error'}`);
            }
        } catch (err) {
            console.error('❌ Logout error:', err);
            alert('❌ Logout failed: ' + err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadWhatsAppQR();
        loadWhatsAppSession();

        setInterval(() => {
            loadWhatsAppQR();
            loadWhatsAppSession();
        }, 20000);

        const logoutBtn = document.getElementById('wa-logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', logoutWhatsAppSession);
        }
    });
</script>
<script>
    // Copy webhook URL to clipboard
    document.getElementById('copyButton').addEventListener('click', function() {
        const webhookUrl = document.getElementById('webhookUrl');
        webhookUrl.select();
        webhookUrl.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(webhookUrl.value);
            
        // Show feedback
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => {
            this.innerHTML = originalHTML;
        }, 2000);
    });
</script>
<script>
  document.getElementById('checkStatusBtn').addEventListener('click', function (e) {
    e.preventDefault(); 
    location.reload();  
  });
</script>



@endpush
