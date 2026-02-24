@extends('backend.layouts.master')
@section('title', __('create_whatsapp_campaign'))
@push('css_asset')
    <link rel="stylesheet" href="{{ static_asset('admin/css/devices.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/template.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .flatpickr-wrapper {
            width: 100%;
        }

        #schedule_time_div {
            display: none;
        }

        .boot-file-input {
            height: 38px !important;
            padding-left: 12px !important;
        }

        .message.received {
            width: 90%;
        }

        .temp-pre {
            width: 320px;
            border-radius: 20px;
            min-height: 350px;
        }

        .conversation .conversation-container {
            min-height: 350px;
        }
    </style>
@endpush
@section('content')
    <div class="main-content-wrapper">
        <section class="oftions">
            <div class="container-fluid">
                <div class="row justify-content-center justify-center">
                    <div class="col-lg-8">
                        <h3 class="section-title">{{ __('verify_whatsapp_number') }}</h3>
                        <form action="{{ route('client.web.whatsapp.verify-number.store') }}" id="campaign_store" method="post"
                            enctype="multipart/form-data" novalidate>
                            {{-- novalidate --}}
                            @csrf
                            <div class="bg-white redious-border p-20 p-sm-30">
                                <div class="row" style="display: flex;">
                                    <div class="col-lg-12" style="display: flex; flex-direction: column;">
                                        <div class="row gx-20 add-coupon">
                                            <input type="hidden" class="is_modal" value="0" />
                                            <div class="col-lg-12">
                                                <div class="mb-4">
                                                    <label for="name" class="form-label">{{ __('name') }}<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control rounded-2" id="name"
                                                        name="name" value="{{ old('name') }}"
                                                        placeholder="{{ __('name') }}" required>
                                                    @if ($errors->has('name'))
                                                        <div class="nk-block-des text-danger">
                                                            <p>{{ $errors->first('name') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="select-type-v2 mb-4 list-space">
                                                    <label for="device_id" class="form-label">{{ __('devices') }}</label>
                                                    <div class="select-type-v1 list-space">
                                                        <select class="form-select form-select-lg rounded-0 mb-3 with_search"
                                                            id="device_id" aria-label=".form-select-lg example" name="device_id" required>
                                                            <option>{{ __('select_device') }}</option>
                                                            @forelse ($devices as $device)
                                                                <option value="{{ $device->id }}"> {{ $device->name }} </option>
                                                            @empty
                                                            <option>{{ __('no_active_device_found') }}</option>
                                                            @endforelse
                                                        </select>
                                                        @if ($errors->has('device_id'))
                                                            <div class="nk-block-des text-danger">
                                                                <p>{{ str_replace('id', '', $errors->first('device_id')) }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="mb-4">
                                                    <label for="contact_list_id" class="form-label">
                                                        {{ __('contact_lists') }}
                                                    </label>
                                                    <select id="contact_list_id" name="contact_list_id[]"
                                                        class="multiple-select-1 form-select-lg rounded-0 mb-3"
                                                        aria-label=".form-select-lg example" multiple>
                                                        <option value="" disabled>{{ __('select_contact_list') }}
                                                        </option>
                                                        @foreach ($contact_lists as $key => $contact_list)
                                                            <option value="{{ $key }}"
                                                                {{ old('contact_list_id') == $key ? 'selected' : '' }}>
                                                                {{ $contact_list }}</option>
                                                        @endforeach
                                                        {{-- <option value="all">{{ __('all_contacts') }}</option> --}}
                                                    </select>
                                                    @if ($errors->has('contact_list_id'))
                                                        <div class="nk-block-des text-danger">
                                                            <p>{{ $errors->first('contact_list_id') }}</p>
                                                        </div>
                                                    @endif
                                                    <small class="form-text text-muted">
                                                        {{ __('to_send_to_all_contact_list_leave_the_selection_blank') }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="mb-4">
                                                    <label for="segment_id" class="form-label">
                                                        {{ __('segment') }}
                                                    </label>
                                                    <select id="segment_id" name="segment_id[]"
                                                        class="multiple-select-1 form-select-lg rounded-0 mb-3"
                                                        aria-label=".form-select-lg example" multiple>
                                                        <option value="" disabled>{{ __('select_segment') }}</option>
                                                        @foreach ($segments as $key => $segment)
                                                            <option value="{{ $key }}"
                                                                {{ old('segment_id') == $key ? 'selected' : '' }}>
                                                                {{ $segment }}</option>
                                                        @endforeach
                                                        {{-- <option value="all" selected>{{ __('all_segment') }}</option> --}}
                                                    </select>
                                                    @if ($errors->has('segment_id'))
                                                        <div class="nk-block-des text-danger">
                                                            <p>{{ $errors->first('segment_id') }}</p>
                                                        </div>
                                                    @endif
                                                    <small class="form-text text-muted">
                                                        {{ __('to_send_to_all_segment_leave_the_selection_blank') }}
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div id="total_contact">{{ __('total_contact') }} : <span
                                                        id="total-display">0</span></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-start align-items-center mt-30">
                                                    <button type="submit"
                                                        class="btn sg-btn-primary">{{ __('verify') }}</button>
                                                    @include('backend.common.loading-btn', [
                                                        'class' => 'btn sg-btn-primary',
                                                    ])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('js_asset')
    <script>
        window.translations = {!! json_encode(json_decode(file_get_contents(base_path('lang/en.json')), true)) !!};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    {{-- <script src="{{ static_asset('admin/js/custom/web-template.js') }}"></script> --}}
    {{-- <script>
        var get_template = "{{ route('client.web.template.get', ['id' => '__template_id__']) }}";
        var contact_count_url = "{{ route('client.web.whatsapp.campaign.count-contact') }}";
        $(document).ready(function() {
            $('#schedule_time').flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                static: true,
                minuteIncrement: 5,
                allowInput: true 
            });

            $(document).on('input', '.live_preview', function() {
                var targetId = $(this).data('target');
                var newValue = $(this).val();
                $(targetId).text(newValue);
            });

        })
    </script> --}}
    <script>
        var contact_count_url = "{{ route('client.web.whatsapp.campaign.count-contact') }}";
        $(document).ready(function() {
            // Initialize Flatpickr once
            $('#schedule_time').flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                static: true,
                minuteIncrement: 5,
                allowInput: true
            });

            // Hide the schedule div initially
            $('#schedule_time_div').hide();

            // Toggle visibility + required state
            $('#send_scheduled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#schedule_time_div').slideDown();
                    $('#schedule_time').attr('required', true);
                } else {
                    $('#schedule_time_div').slideUp();
                    $('#schedule_time').removeAttr('required').val('');
                }
            });

            // ✅ Extra protection: ensure hidden fields are never validated
            $('form').on('submit', function(e) {
                if (!$('#send_scheduled').is(':checked')) {
                    $('#schedule_time').removeAttr('required');
                }
            });
        });
    </script>


    <script src="{{ static_asset('admin/js/custom/campaign.js') }}"></script>
    <script src="{{ static_asset('admin/js/custom/campaign/create.js') }}"></script>
@endpush
