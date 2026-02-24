@extends('backend.layouts.master')
@section('title', __('whatsApp_settings'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="default-tab-list default-tab-list-v2  bg-white redious-border p-20 p-sm-30 mb-4">
                    

                    <div class="row mb-3 justify-content-center">
                        <div class="col-lg-12">
                            <div class=" h-100">
                                <div class="">
                                    <h5><i class="lab la-whatsapp"></i> {{ __('unlimited_messaging_with_rapiwa') }}</h5>
                                </div>
                                <div class="">
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
                                            {{-- <a href="{{ route('client.web.settings.sync', @Auth::user()->client->id) }}" id="checkStatusBtn" class="btn btn-outline-secondary">Check Status</a>                     --}}
                                            
                                            @if (
                                                setting('whatsapp_web_access') === 'admin' &&
                                                optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
                                            )
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    id="sync_button"
                                                    data-id="{{ @Auth::user()->client->id }}"
                                                    data-url="{{ route('client.web.settings.sync.admin-handle', $data->whatsapp_session) }}"
                                                    >
                                                    <i class="las la-sync-alt"></i> {{ __('sync') }}
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    id="sync_button"
                                                    data-id="{{ @Auth::user()->client->webSetting->id }}"
                                                    data-url="{{ route('client.web.settings.sync', @Auth::user()->client->webSetting->client_id) }}">
                                                    <i class="las la-sync-alt"></i> {{ __('Check Status') }}
                                                </button>
                                            @endif
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
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>

<script>
    const rapiwaDeviceKey = '{{ $data->whatsapp_session }}';
    // const baseUrl = '{{ @Auth::user()->client->webSetting->app_id }}';
    const baseUrl = 'https://app.rapiwa.com';

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
                    'Authorization': `Bearer ${rapiwaDeviceKey}`
                }
            });

            const html = await response.text();
            container.innerHTML = html;

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
                    'Authorization': `Bearer ${rapiwaDeviceKey}`
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
                    "Authorization": `Bearer ${rapiwaDeviceKey}`,
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
        }, 50000);

        const logoutBtn = document.getElementById('wa-logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', logoutWhatsAppSession);
        }
    });
</script>

@endpush

@push('js')
    <script>
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
