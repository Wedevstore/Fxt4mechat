@extends('backend.layouts.master')
@section('title', __('warm_up'))
@section('content')
    <section class="oftions">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-top d-flex justify-content-between align-items-center">
                        <h3 class="section-title">{{__('warm_up') }}</h3>
                        <div class="oftions-content-right mb-12">
                            <a href="{{ route('client.web.whatsapp.warmup.run') }}" class="d-flex align-items-center btn sg-btn-primary gap-2">
                                <i class="las la-bullhorn"></i>
                                <span>{{__('run_warm_up_job') }}</span>
                            </a>
                            <a href="{{ route('client.web.whatsapp.warm-up.create') }}" class="d-flex align-items-center btn sg-btn-primary gap-2">
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
        $(document).on('change', '.warm-up-status-change', function () {
            let id = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('client.web.whatsapp.warm-up.status-change') }}", // We'll define this route below
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status,
                },
                success: function (response) {
                    if (response.status === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("Something went wrong!");
                }
            });
        });
    </script>
@endpush
