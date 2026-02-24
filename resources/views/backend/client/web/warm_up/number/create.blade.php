@extends('backend.layouts.master')
@section('title', __('add_new_reply'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h3 class="section-title">{{ __('add_new_number') }}</h3>
                <div class="bg-white redious-border p-20 p-sm-30">
                    <form action="{{ route('client.web.whatsapp.warm-up-number.store') }}" class="form-validate form" method="POST">
                        @csrf
                        <div class="row gx-20">
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <div class="select-type-v2">
                                        <label for="name" class="form-label mb-1">{{ __('name') }}<span
                                                class="text-danger">*</span></label>
                                        <input class="form-control mb-3" type="text" name="name" id="name"
                                            placeholder="{{ __('name') }}" required>
                                        <div class="nk-block-des text-danger">
                                            <p class="name_error error">{{ $errors->first('name') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <div class="select-type-v2">
                                        <label for="phone_number" class="form-label mb-1">{{ __('phone_number') }}<span
                                                class="text-danger">*</span></label>
                                        <input class="form-control mb-3" type="hidden" name="warmup_id" id="warmup_id"
                                            value="{{ $warmUp->id }}" required>
                                        <input class="form-control mb-3" type="text" name="phone_number" id="phone_number"
                                            placeholder="{{ __('phone_number') }}" required>
                                        <div class="nk-block-des text-danger">
                                            <p class="phone_number_error error">{{ $errors->first('phone_number') }}</p>
                                        </div>
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
