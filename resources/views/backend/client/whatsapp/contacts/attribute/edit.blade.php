@extends('backend.layouts.master')
@section('title', __('edit_reply'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="section-title">{{ __('edit_reply') }}</h3>
                <div class="bg-white redious-border p-20 p-sm-30">
                    <form action="{{ route('client.contact-attributes.update', $contact->id) }}" class="form"
                        method="POST">
                        @csrf
                        <div class="row gx-20">
                            <div class="col-lg-4">
                                <div class="mb-4">
                                    <div class="select-type-v2">
                                        <label for="title" class="form-label mb-1">{{ __('title') }}<span
                                                class="text-danger">*</span></label>
                                        <input class="form-control mb-3" type="text" name="title" id="title"
                                            placeholder="{{ __('title') }}" value="{{ $contact->title }}">
                                        <div class="nk-block-des text-danger">
                                            <p class="title_error error">{{ $errors->first('title') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="select-type-v2 mb-4 list-space">
                                    <label for="type" class="form-label">{{ __('type') }}</label>
                                    <div class="select-type-v1 list-space">
                                        <select class="form-select form-select-lg rounded-0 mb-3 with_search"
                                            id="type" aria-label=".form-select-lg example" name="type">
                                            <option value="">{{ __('select_type') }}</option>
                                            @foreach (config('static_array.custom_input_types') as $key=> $input)
                                            <option value="{{ $key }}"  {{ $contact->type == $key ? 'selected' : '' }}>{{ __($input) }}</option>
                                            @endforeach
                                        
                                        </select>
                                        @if ($errors->has('type'))
                                            <div class="nk-block-des text-danger">
                                                <p>{{ str_replace('id', '', $errors->first('type')) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-12 sandbox_mode_div">
                                <input type="hidden" name="status" value="{{ $contact->status }}">
                                <label class="form-label"
                                       for="status">{{ __('status') }}</label>
                                <div class="setting-check">
                                    <input type="checkbox" value="1" id="status"
                                           class="sandbox_mode" {{ $contact->status == 1 ? 'checked' : '' }}>
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

@push('js')

@endpush
