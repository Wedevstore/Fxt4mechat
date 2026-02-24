@extends('backend.layouts.master')
@section('title', __('ai_writter_setting'))
@section('content')
    <div class="row justify-content-md-center">
        <div class="col col-lg-9 col-md-9">
            <h3 class="section-title">{{ __('ai_writer_setting') }}</h3>
            <div class="bg-white redious-border p-20 p-sm-30">
                <form action="{{ route('client.ai.writer') }}" class="form-validate form" method="POST">
                    @csrf
                    <input type="hidden" class="is_modal" value="0" />
                    <div class="col-12">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label for="secret_key" class="form-label">{{ __('secret_key') }} <span
                                        class="text-danger">*</span></label>
                                <a href="https://platform.openai.com/account/api-keys"
                                    target="_blank">{{ __('click_here_to_get_the_key') }}</a>
                            </div>
                            <input type="text" class="form-control rounded-2" id="secret_key" name="ai_secret_key"
                                value="{{ isDemoMode() ? '******************' : @Auth::user()->client->open_ai_key }}"
                                placeholder="{{ __('enter_secret_key') }}" required>
                            <div class="nk-block-des text-danger">
                                <p class="secret_key_error error">{{ $errors->first('secret_key') }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center mt-30">
                            <button type="submit" class="btn sg-btn-primary">{{ __('submit') }}</button>
                            @include('backend.common.loading-btn', ['class' => 'btn sg-btn-primary'])
                        </div>
                    </div>
                </form>
                @if (@Auth::user()->client->open_ai_key)
                <h6 class="sub-title mt-40">{{ __('ai_reply') }}</h6>
                <div class="d-flex justify-content-between mb-40">
                    <label for="checkbox1">{{ __('is_enable_ai_reply') }}</label>
                    <div class="setting-check">
                        <input type="checkbox" id="checkbox1" data-url="{{ route('client.setting.ai-reply.status-update') }}" data-field_for="is_enable_ai_reply" name="is_enable_ai_reply"
                             class="ai_reply_status"
                            {{ @Auth::user()->client->is_enable_ai_reply == 1 ? 'checked' : '' }}>
                        <label for="checkbox1"></label>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $('.ai_reply_status').on('change', function() {
            var isChecked = $(this).is(':checked') ? 1 : 0;
            var updateUrl = $(this).data('url');
            var field = $(this).data('field_for');
            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field: field,
                    value: isChecked
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
            });
        });
    });
</script>
@endpush