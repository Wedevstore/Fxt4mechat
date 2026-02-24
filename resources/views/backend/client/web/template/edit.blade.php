@extends('backend.layouts.master')
@section('title', __('edit_whatsapp_web_template'))
@section('content')
<style>
    .bg-custom-template-height {
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
                    <h3 class="section-title">{{ __('edit_whatsapp_web_template') }}</h3>
                    <a href="{{ route('client.web.templates.index') }}" class="btn btn-secondary">
                        <i class="las la-arrow-left"></i> {{ __('back') }}
                    </a>
                </div>

                <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30 bg-custom-template-height">
                    <div class="row">
                        <div class="col-lg-8">
                            <form method="POST" action="{{ route('client.web.template.update', $template->id) }}"
                                id="whatsapp-template-form" enctype="multipart/form-data">
                                @csrf
                                @method('post')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="template_name">{{ __('template_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="template_name"
                                                name="template_name" value="{{ $template->name }}" placeholder="{{ __('enter_template_name') }}"
                                                value="{{ old('template_name', $template->template_name) }}" maxlength="512" required>
                                            <small id="nameCharCount" class="text-muted text-end">{{ __('characters') }}: 0 / 512</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="message_type">{{ __('message_type') }} <span class="text-danger">*</span></label>
                                            <select class="form-control" id="message_type" name="message_type">
                                                <option value="TEXT" {{ $template->message_type == 'text' ? 'selected' : '' }}>{{ __('text') }}</option>
                                                <option value="IMAGE" {{ $template->message_type == 'image' ? 'selected' : '' }}>{{ __('image') }}</option>
                                                <option value="VIDEO" {{ $template->message_type == 'video' ? 'selected' : '' }}>{{ __('video') }}</option>
                                                <option value="AUDIO" {{ $template->message_type == 'audio' ? 'selected' : '' }}>{{ __('audio') }}</option>
                                                <option value="DOCUMENT" {{ $template->message_type == 'document' ? 'selected' : '' }}>{{ __('document') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Header Media Sections --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div id="headerImageSection" class="mb-4" style="display: {{ $template->message_type == 'image' ? 'block' : 'none' }};">
                                            <label for="header_image">{{ __('image') }}</label>
                                            @if ($template->media_url)
                                                <div class="mb-3">
                                                    <img src="{{ asset($template->media_url) }}" alt="Template Image" class="img-fluid rounded" width="200">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control header_file" id="header_image"
                                                name="header_image" accept="image/*">
                                            <small class="text-muted">{{ __('only_image_file') }}</small>
                                        </div>
                                        <div id="headerAudioSection" class="mb-4" style="display: {{ $template->message_type == 'audio' ? 'block' : 'none' }};">
                                            <label for="header_audio">{{ __('audio') }}</label>
                                            @if ($template->media_url)
                                                <div class="mb-3">
                                                    <audio controls>
                                                        <source src="{{ asset($template->media_url) }}" type="audio/mpeg">
                                                        {{ __('your_browser_does_not_support_audio') }}
                                                    </audio>
                                                </div>
                                            @endif
                                            <input type="file" class="form-control header_file" id="header_audio"
                                                name="header_audio" accept="audio/*">
                                            <small class="text-muted">{{ __('only_audio_file') }}</small>
                                        </div>
                                        <div id="headerVideoSection" class="mb-4" style="display: {{ $template->message_type == 'video' ? 'block' : 'none' }};">
                                            <label for="header_video">{{ __('video') }}</label>
                                            @if ($template->media_url)
                                                <div class="mb-3">
                                                    <video controls width="250">
                                                        <source src="{{ asset($template->media_url) }}" type="video/mp4">
                                                        {{ __('your_browser_does_not_support_video') }}
                                                    </video>
                                                </div>
                                            @endif
                                            <input type="file" class="form-control header_file" id="header_video"
                                                name="header_video" accept="video/*">
                                            <small class="text-muted">{{ __('only_video_files') }}</small>
                                        </div>
                                        <div id="headerDocumentSection" class="mb-4" style="display: {{ $template->message_type == 'document' ? 'block' : 'none' }};">
                                            <label for="header_document">{{ __('document') }}</label>
                                            @if ($template->media_url)
                                                <div class="mb-3">
                                                    <a href="{{ asset($template->media_url) }}" target="_blank" class="btn btn-outline-info">
                                                        <i class="las la-file-pdf"></i> {{ __('view_existing_document') }}
                                                    </a>
                                                </div>
                                            @endif
                                            <input type="file" class="form-control header_file"
                                                id="header_document" name="header_document" accept=".pdf">
                                            <small class="text-muted">{{ __('only_pdf_files') }}</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Message Body --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-4">
                                            <label for="message_body">{{ __('message') }}</label>
                                            <textarea class="form-control live_preview" id="message_body" maxlength="1024"
                                                name="message_body" placeholder="{{ __('type_your_message_here') }}">{{ old('message_body', $template->message) }}</textarea>
                                            <small id="charCount" class="text-muted">{{ __('characters') }}: 0</small>
                                        </div>

                                        <div id="sample-body" class="sample-body" style="display: none">
                                            <div class="card c-card">
                                                <div class="card-header">
                                                    {{ __('samples_for_body_content') }} <br>
                                                    <small>{{ __('template_body_sample_notice') }}</small>
                                                </div>
                                                <div class="card-body" id="sample-body-contant"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Button --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-4 mt-2">
                                            <div class="d-flex justify-content-end align-items-center">
                                                <button id="preloader" class="btn btn-primary d-none" type="button" disabled>
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading...
                                                </button>
                                                <button type="submit" class="btn btn-primary save">{{ __('update') }}</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    <script src="{{ static_asset('admin/js/custom/web-template.js') }}?v=1.9.0"></script>

    <script>
        window.addEventListener('scroll', function() {
            const container = document.querySelector('.whatsapp-container');
            const whatsappPreview = document.querySelector('.whatsapp-preview');
            if (container.getBoundingClientRect().top < 0) {
                whatsappPreview.style.top = '0';
            } else {
                whatsappPreview.style.top = '50%';
            }
        });

        $(document).on('input', '.live_preview', function() {
            var targetId = $(this).data('target');
            var newValue = $(this).val();
            $(targetId).text(newValue);
        });
    </script>


    <script>
        function previewHeaderImage(event) {
            const input = event.target;
            const reader = new FileReader();
            reader.onload = function() {
                const img = document.getElementById('previewImage');
                if (img) {
                    img.src = reader.result;
                } else {
                    const newImg = document.createElement('img');
                    newImg.id = 'previewImage';
                    newImg.src = reader.result;
                    newImg.className = 'img-fluid rounded mt-3';
                    newImg.width = 200;
                    input.parentNode.insertBefore(newImg, input);
                }
            }
            if (input.files && input.files[0]) {
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

@endpush



