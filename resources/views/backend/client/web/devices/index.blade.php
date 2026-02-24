@extends('backend.layouts.master')
@section('title', __('Devices'))
@section('content')
<style>
    .card-status {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-badge {
        padding: 0.25em 0.5em;
        border-radius: 0.35rem;
        font-size: 0.75rem;
    }
    .disconnected {
        background-color: #f8d7da;
        color: #842029;
        font-weight: 500;
    }
    .connected {
        background-color: #94ec9b;
        font-weight: 500;
        color: #556068;
    }
    .disabled-text {
        color: #d63384;
        font-weight: 500;
    }
    .platform-card .card-body {
        padding: 1rem;
    }
    .platform-actions button {
        border: none;
        background: none;
        margin-right: 0.5rem;
    }
    .platform-actions button i {
        font-size: 1.2rem;
    }
    button#copyWebhookBtn {
        padding: 8px 7px;
    }
</style>
<div class="container my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-xxl-0">
                    <div class="bg-white redious-border p-4 analytics-box">
                        <div class="analytics clr-1">
                            <div class="analytics-icon">
                                <i class="las la-landmark"></i>
                            </div>
                            <div class="analytics-content">
                                <h4>{{ $statusCounts['total'] }}</h4>
                                <p>{{ __('devices') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-xxl-0">
                    <div class="bg-white redious-border p-4 analytics-box">
                        <div class="analytics clr-2">
                            <div class="analytics-icon">
                                <i class="lar la-thumbs-up"></i>
                            </div>
                            <div class="analytics-content">
                                <h4>{{ $statusCounts['pending'] }}</h4>
                                <p>{{ __('pending') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-lg-0">
                    <div class="bg-white redious-border p-4 analytics-box">
                        <div class="analytics clr-3">
                            <div class="analytics-icon">
                                <i class="las la-user"></i>
                            </div>
                            <div class="analytics-content">
                                <h4>{{ $statusCounts['connected'] }}</h4>
                                <p>{{ __('connected') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-lg-6 col-md-6">
                    <div class="bg-white redious-border p-4 analytics-box">
                        <div class="analytics clr-4">
                            <div class="analytics-icon">
                                <i class="las la-ban"></i>
                            </div>
                            <div class="analytics-content">
                                <h4>{{ $statusCounts['logged_out'] }}</h4>
                                <p>{{ __('logged_out') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platforms List -->
    {{-- <div class="d-flex justify-content-between align-items-center mb-3 my-5">
        <h5>Devices</h5>
        <a href="https://app.rapiwa.com/client/session" target="__blank" class="btn btn-success">Add New</a>
    </div> --}}

    <div class="d-flex justify-content-between align-items-center mb-3 my-5">
        <h3 class="section-title">{{ __('devices') }}</h3>
        @if (
            setting('whatsapp_web_access') === 'admin' &&
            optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
        )
        <div class="oftions-content-right mb-12 gap-2">
            <a href="#" data-bs-toggle="modal" data-bs-target="#createSessionModal"
                class="d-flex align-items-center btn sg-btn-primary">
                <i class="las la-plus"></i>
                <span>{{ __('add_device') }}</span>
            </a>
        </div>
        @endif
    </div> 

    <!-- Add Device Modal -->
    <div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
        <div class="row justify-content-center">
            {{-- <div class="col-md-6"> --}}
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="width: 500px">
                        <form method="POST" action="{{ route('client.web.device.store') }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="createSessionModalLabel">{{ __('create_device') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('device_name') }}</label>
                                    <input type="text" placeholder="{{ __('enter_device_name') }}" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('whatsapp_nummber') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="phone_number" class="form-control" placeholder="{{ __('enter_whatsapp_number_with_country_code') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="align-items-center btn sg-btn-primary">
                                    <i class="fa-solid fa-qrcode"></i> {{ __('create') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            {{-- </div> --}}
        </div>
    </div>


    <div class="row my-5">
        @forelse ($devices as $device)
            <div class="col-md-4">
                <div class="card border-1 rounded-4 mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5>{{ $device->name }}</h5>
                                <p class="my-1">{{ isDemoMode() ? '+88017*********' : $device->phone_number }}</p>
                                <span class="status-badge {{ $device->status === 'connected' ? 'connected' : 'disconnected' }}">{{ __($device->status) }}</span>
                            </div>
                            <div class="platform-actions">
                                <button type="button" 
                                        title="Message" 
                                        onclick="window.location.href='{{ route('client.web.devices.chat.setting', $device->id) }}'">
                                    <i class="las la-comment"></i>
                                </button>
                                <button type="button" 
                                        title="Settings" 
                                        onclick="window.location.href='{{ route('client.web.devices.setting', $device->id) }}'">
                                    <i class="las la-cog"></i>
                                </button>
                                <button type="button" title="Webhook" data-bs-toggle="modal"
                                    data-bs-target="#webhookModal"
                                    data-webhook-url="{{ isDemoMode() ? '***************************************' : route('whatsapp.web.webhook', $device->whatsapp_session) }}">
                                    <i class="las la-project-diagram"></i>
                                </button>
                            </div>
                            
                        </div>
                        @if (
                            setting('whatsapp_web_access') === 'admin' &&
                            optional(auth()->user()->client->activeSubscription)->whatsapp_web_access == 1
                        )
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    {{ __("available_credits") }}
                                </div>
                                @php
                                    $totalCredits = optional($device->credit)->total_credits ?? 0;
                                    $usedCredits  = optional($device->credit)->used_credits ?? 0;
                                    $availableCredits = max($totalCredits - $usedCredits, 0);
                                @endphp

                                <div class="fw-bold text-primary">
                                    <i class="las la-coins"></i>
                                    {{ isDemoMode() ? '5000' : $availableCredits }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5>{{ __("device_not_available") }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse


    </div>

    {{-- Webhook Modal --}}
    <div class="modal fade" id="webhookModal" tabindex="-1" aria-labelledby="webhookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="webhookModalLabel">Webhook URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Use this webhook URL to connect your system to rapiwa:</p>
                    <div class="input-group">
                        <input type="text" id="webhookUrl" class="form-control text-center" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyWebhookBtn">
                            <i class="las la-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">Click “Copy” to get your webhook URL, then add it to the Rapiwa device webhook field & save it.</small>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const webhookModal = document.getElementById('webhookModal');
        webhookModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const webhookUrl = button.getAttribute('data-webhook-url');
            document.getElementById('webhookUrl').value = webhookUrl;
        });

        document.getElementById('copyWebhookBtn').addEventListener('click', function () {
            const input = document.getElementById('webhookUrl');
            input.select();
            document.execCommand('copy');
            this.innerHTML = '<i class="las la-check"></i> Copied!';
            setTimeout(() => {
                this.innerHTML = '<i class="las la-copy"></i> Copy';
            }, 1500);
        });
    });
</script>
@endpush

