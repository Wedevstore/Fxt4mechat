@extends('backend.layouts.master')
@section('title', __('web_templates'))
@section('content')
<style>
    .bg-custom-template-height{
        height: 745px;
    }
</style>
    @push('css_asset')
        <link rel="stylesheet" href="{{ static_asset('admin/css/devices.min.css') }}">
        <link rel="stylesheet" href="{{ static_asset('admin/css/template.css') }}">
    @endpush
    <section class="oftions">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col col-lg-12 col-md-12">
                    <div class="d-flex align-items-center justify-content-between mb-12">
                        <h3 class="section-title">{{ __('create_whatsapp_web_template') }}</h3>
                    </div>
                    <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30 bg-custom-template-height">
                        <div class="row">
                            <div class="col-lg-8">
                                <form method="POST" action="{{ route('client.web.template.store') }}"
                                    id="whatsapp-template-form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="template_name">{{ __('template_name') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="template_name"
                                                    name="template_name" placeholder="{{ __('enter_template_name') }}"
                                                    value="{{ old('template_name') }}" maxlength="512" required>
                                                <div class="invalid-feedback text-danger"></div>
                                                <small id="nameCharCount"
                                                    class="text-muted text-end">{{ __('characters') }}: 0 /
                                                    512</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="message_type">{{ __('message_type') }} <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control" id="message_type" name="message_type">
                                                    <option value="TEXT"
                                                        {{ old('message_type') == 'TEXT' ? 'selected' : '' }}>
                                                        {{ __('text') }}
                                                    </option>
                                                    <option value="IMAGE"
                                                        {{ old('message_type') == 'IMAGE' ? 'selected' : '' }}>
                                                        {{ __('image') }}
                                                    </option>
                                                    <option value="VIDEO"
                                                        {{ old('message_type') == 'VIDEO' ? 'selected' : '' }}>
                                                        {{ __('video') }}
                                                    </option>
                                                    <option value="AUDIO"
                                                        {{ old('message_type') == 'AUDIO' ? 'selected' : '' }}>
                                                        {{ __('audio') }}
                                                    </option>
                                                    <option value="DOCUMENT"
                                                        {{ old('message_type') == 'DOCUMENT' ? 'selected' : '' }}>
                                                        {{ __('document') }}
                                                    </option>
                                                </select>
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>
                                        </div>                                       
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="headerImageSection" class="mb-4"
                                                style="display: {{ old('message_type') == 'IMAGE' ? 'block' : 'none' }};">
                                                <label for="header_image">{{ __('image') }}</label>
                                                <input type="file" class="form-control header_file" id="header_image"
                                                    name="header_image" placeholder="{{ __('enter_header_image') }}"
                                                    accept="image/*" value="{{ old('header_image') }}">
                                                <small class="text-muted">{{ __('only_image_file') }}</small>
                                                <div class="invalid-feedback text-danger">
                                                </div>
                                            </div>

                                            <div id="headerAudioSection" class="mb-4"
                                                style="display:  {{ old('message_type') == 'AUDIO' ? 'block' : 'none' }};">
                                                <label for="header_audio">{{ __('audio') }}</label>
                                                <input type="file" class="form-control header_file" id="header_audio"
                                                    name="media_url" placeholder="{{ __('enter_header_audio') }}"
                                                    accept="audio/*" value="{{ old('header_audio') }}">
                                                <small class="text-muted">{{ __('only_audio_file') }}</small>
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>

                                            <div id="headerVideoSection" class="mb-4"
                                                style="display: {{ old('message_type') == 'VIDEO' ? 'block' : 'none' }};">
                                                <label for="header_video">{{ __('video') }}</label>
                                                <input type="file" class="form-control header_file" id="header_video"
                                                    name="header_video" placeholder="{{ __('enter_header_video') }}"
                                                    accept="video/*" value="{{ old('header_video') }}">
                                                <div id="validationMessage" class="invalid-feedback text-danger"></div>

                                                <small class="text-muted">{{ __('only_video_files') }}</small>
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>

                                            <div id="headerDocumentSection" class="mb-4"
                                                style="display: {{ old('message_type') == 'DOCUMENT' ? 'block' : 'none' }};">
                                                <label for="header_document">{{ __('document') }}</label>
                                                <input type="file" class="form-control header_file"
                                                    id="header_document" name="header_document"
                                                    placeholder="{{ __('enter_header_document') }}" accept=".pdf"
                                                    value="{{ old('header_document') }}">
                                                <small class="text-muted">{{ __('only_pdf_files') }}</small>
                                                <div id="pdfValidationMessage" class="invalid-feedback text-danger"></div>
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label for="message_body">{{ __('message') }}
                                                    <span class="text-danger"></span>
                                                </label>
                                                <textarea class="form-control" id="message_body" maxlength="1024" name="message_body"
                                                    placeholder="{{ __('type_your_message_here') }}">{{ old('message_body') }}</textarea>
                                                <div class="invalid-feedback text-danger"></div>
                                                <small id="charCount" class="text-muted">{{ __('characters') }}: 0</small>
                                            </div>
                                            <div id="sample-body" class="sample-body" style="display: none">
                                                <div class="card c-card">
                                                    <div class="card-header">
                                                        {{ __('samples_for_body_content') }}
                                                        <br>
                                                        <span><small>{{ __('template_body_sample_notice') }}</small></span>
                                                    </div>
                                                    <div class="card-body" id="sample-body-contant">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4 mt-2">
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <button id="preloader" class="btn btn-primary d-none" type="button"
                                                        disabled>
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                        Loading...
                                                    </button>
                                                    <button type="submit"
                                                        class="btn btn-primary save">{{ __('submit') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <div class="whatsapp-container">
                                    @include('backend.client.web.template.partials._preview')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('backend.client.whatsapp.template.partials._category_modal')
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
    <script>
        window.translations = {!! json_encode(json_decode(file_get_contents(base_path('lang/en.json')), true)) !!};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    <script src="{{ static_asset('admin/js/custom/web-template.js') }}?v=1.9.0"></script>
    <script>
        window.addEventListener('scroll', function() {
            const container = document.querySelector('.whatsapp-container');
            const whatsappPreview = document.querySelector('.whatsapp-preview');

            if (container.getBoundingClientRect().top < 0) {
                whatsappPreview.style.top = '0'; // Stick to top of container when scrolled
            } else {
                whatsappPreview.style.top = '50%'; // Center vertically when not scrolled
            }
        });

        $(document).on('input', '.live_preview', function() {
                var targetId = $(this).data('target');
                var newValue = $(this).val();
                $(targetId).text(newValue);
            });
    

    </script>
@endpush
