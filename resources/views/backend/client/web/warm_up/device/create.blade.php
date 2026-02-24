@extends('backend.layouts.master')
@section('title', __('add_new_reply'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h3 class="section-title">{{ __('add_new_warmup_device_for_messages') }}</h3>
                <div class="bg-white redious-border p-20 p-sm-30">
                    <form action="{{ route('client.web.whatsapp.warm-up-device.store') }}" class="form-validate form" method="POST">
                        @csrf
                        <div class="row gx-20">
                            <div class="col-lg-12" style="display: none;">
                                <div class="mb-4">
                                    <div class="select-type-v2">
                                        <label for="name" class="form-label mb-1">{{ __('warmup_id') }}<span
                                                class="text-danger">*</span></label>
                                        <input class="form-control mb-3" type="number" name="warmup_id" id="warmup_id"
                                            value="{{ $warmUp->id }}" required>
                                    </div>
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
                                                    @continue($warmUp->device_id == $device->id)
                                                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                                                @empty
                                                    <option>{{ __('no_device_found') }}</option>
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
                            <div class="d-flex gap-12 sandbox_mode_div">
                                <input type="hidden" name="status" value="1">
                                <label class="form-label"
                                       for="status">{{ __('status') }}</label>
                                <div class="setting-check">
                                    <input type="checkbox" value="1" id="status"
                                           class="sandbox_mode" checked>
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
