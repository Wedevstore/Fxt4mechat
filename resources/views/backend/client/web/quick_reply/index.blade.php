@extends('backend.layouts.master')
@section('title', __('bot_&_quick_replies_management'))
@section('content')
    <section class="oftions">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-top d-flex justify-content-between align-items-center">
                        <h3 class="section-title">{{__('quick_replies_management') }}</h3>
                        <div class="oftions-content-right mb-12">
                            <a href="{{ route('client.web.whatsapp.quick-reply.create') }}" class="d-flex align-items-center btn sg-btn-primary gap-2">
                                <i class="las la-plus"></i>
                                <span>{{__('add_new') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="bg-white rounded-20 p-20 p-sm-30">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="default-list-table table-responsive yajra-dataTable">
                                    {{ $dataTable->table() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for Sample Replies -->
<div class="modal fade" id="sampleRepliesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Sample Replies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="sampleRepliesList"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="storeSelectedReplies">Save Selected Replies</button>
            </div>
        </div>
    </div>
</div>
    @include('backend.common.delete-script')

@endsection
@push('js')
    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {
            // Show sample replies in modal
            $('#showSampleReplies').click(function() {
                const samples = @json(config('static_array.bot_replies'));
                $('#sampleRepliesList').empty();
    
                samples.forEach((sample, index) => {
                    $('#sampleRepliesList').append(`
                        <li class="list-group-item">
                            <input type="checkbox" class="sample-checkbox" data-index="${index}" />
                            ${sample.name} - ${sample.reply_text}
                        </li>
                    `);
                });
    
                $('#sampleRepliesModal').modal('show');
            });
    
            // Handle save selected replies
            $('#storeSelectedReplies').click(function() {
                const selectedSamples = [];
    
                $('.sample-checkbox:checked').each(function() {
                    const index = $(this).data('index');
                    selectedSamples.push({
                        name: samples[index].name,
                        reply_text: samples[index].reply_text,
                        reply_type: samples[index].reply_type,
                        reply_using_ai: samples[index].reply_using_ai,
                        keywords: samples[index].keywords
                    });
                });
    
                $.ajax({
                    url: '{{ route('client.bot-reply.store') }}',
                    type: 'POST',
                    data: {
                        replies: selectedSamples,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Samples saved successfully!');
                        location.reload();
                    },
                    error: function() {
                        alert('Error saving samples.');
                    }
                });
            });
        });
    </script>
    

@endpush
