@extends('backend.layouts.master')
@section('title', __('edit_warm_up'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h3 class="section-title">{{ __('edit_warm_up') }}</h3>
                <div class="bg-white redious-border p-20 p-sm-30">
                    <form action="{{ route('client.web.whatsapp.warm-up.update', $warmUp->id) }}" class="form-validate form"
                        method="POST">
                        @csrf
                        <div class="row gx-20">
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <div class="select-type-v2">
                                        <label for="name" class="form-label mb-1">{{ __('name') }}<span
                                                class="text-danger">*</span></label>
                                        <input class="form-control mb-3" required type="text" name="name" id="name"
                                            placeholder="{{ __('name') }}" value="{{ $warmUp->name }}">
                                        <div class="nk-block-des text-danger">
                                            <p class="name_error error">{{ $errors->first('name') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-lg-12">
                                <div class="select-type-v2 mb-4 list-space">
                                    <label for="device" class="form-label">{{ __('devices') }}</label>
                                    <div class="select-type-v1 list-space">
                                        <select class="form-select form-select-lg rounded-0 mb-3 with_search"
                                            id="device" aria-label=".form-select-lg example" name="device_id" required>
                                            <option>{{ __('select_device') }}</option>
                                            @forelse ($devices as $device)
                                                <option @if ($device->id == $warmUp->device_id) selected @endif value="{{ $device->id }}"> {{ $device->name }} </option>
                                            @empty
                                                <option>{{ __('no_device_found') }}</option>
                                            @endforelse
                                        </select>
                                        @if ($errors->has('device'))
                                            <div class="nk-block-des text-danger">
                                                <p>{{ str_replace('id', '', $errors->first('device')) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div> --}}

                            <!-- First dropdown -->
                            <div class="mb-4">
                                <label for="device_id" class="form-label">{{ __('warmup_device') }}</label>
                                <select class="form-select form-select-lg rounded-0 with_search" id="device_id" name="device_id" required>
                                    <option value="">{{ __('select_device') }}</option>
                                    @foreach ($devices as $device)
                                        <option value="{{ $device->id }}" {{ $warmUp->device_id == $device->id ? 'selected' : '' }}>
                                            {{ $device->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Second dropdown -->
                            <div class="mb-4">
                                <label for="device_list_id" class="form-label">{{ __('select_device_for_help_warmup') }}</label>
                                <select id="device_list_id" name="device_list_id[]" class="form-select form-select-lg rounded-0" multiple>
                                    @foreach ($devices as $device)
                                        @if ($device->id != $warmUp->device_id)
                                            <option value="{{ $device->id }}" {{ in_array($device->id, $selectedHelpers) ? 'selected' : '' }}>
                                                {{ $device->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-12 sandbox_mode_div">
                                <input type="hidden" name="status" value="{{ $warmUp->status }}">
                                <label class="form-label"
                                       for="status">{{ __('status') }}</label>
                                <div class="setting-check">
                                    <input type="checkbox" value="1" id="status"
                                           class="sandbox_mode" {{ $warmUp->status == 1 ? 'checked' : '' }}>
                                    <label for="status"></label>
                                </div>
                            </div>
                            
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-40">
                            <button type="submit" class="btn sg-btn-primary">{{ __('save') }}</button>
                            @include('backend.common.loading-btn', ['class' => 'btn sg-btn-primary'])
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    if ($.fn.select2) {
        $('#device_id').select2({
            placeholder: "Select main device",
            width: '100%'
        });

        $('#device_list_id').select2({
            placeholder: "Select helper devices",
            width: '100%'
        });
    }

    const $deviceSingle = $('#device_id');
    const $deviceMulti = $('#device_list_id');
    const originalOptions = $deviceMulti.find('option').clone();

    function updateSecondSelect() {
        const selected = $deviceSingle.val();
        $deviceMulti.empty().append(originalOptions.clone());
        if (selected) {
            $deviceMulti.find('option[value="' + selected + '"]').remove();
        }
        $deviceMulti.trigger('change.select2');
    }

    $deviceSingle.on('change', updateSecondSelect);
    updateSecondSelect();
});
</script>
