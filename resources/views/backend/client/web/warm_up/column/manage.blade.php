@extends('backend.layouts.master')
@section('title', __('campaigns'))
@section('content')
    <section class="oftions">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-top d-flex justify-content-between align-items-center">
                        <h3 class="section-title">{{ @$warmUp->name }}</h3>
                        <div class="oftions-content-right mb-12">
                            <a href="{{ route('client.web.whatsapp.warm-up-number.create', $warmUp->id) }}" class="d-flex align-items-center btn sg-btn-primary gap-2">
                                <i class="las la-plus"></i>
                                <span>{{__('add_warm_up_number') }}</span>
                            </a>
                            <a href="#" 
                                class="d-flex align-items-center btn sg-btn-primary gap-2"
                                data-bs-toggle="modal" 
                                data-bs-target="#helpingWarmUpModal">
                                <i class="las la-eye"></i>
                                <span>{{ __('show_helping_warm_up_device') }}</span>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="helpingWarmUpModal" tabindex="-1" aria-labelledby="helpingWarmUpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="helpingWarmUpModalLabel">{{ __('helping_warmup_devices') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('name') }}</th>
                                        <th>{{ __('number') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($warmUpDevices as $index => $warmUpDevice)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $warmUpDevice->name }}</td>
                                            <td>{{ $warmUpDevice->phone_number }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Device not found</td>
                                        </tr>
                                    @endforelse
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="oftions">
        <div class="row">
            <div class="col-lg-12">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="redious-border mb-40 p-5 p-sm-20 bg-white" style="position: relative;">
                                <div class="mt-4"></div>
                                <div class="row">
                                    <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-xxl-0">
                                        <div class="bg-white redious-border p-20 p-sm-30">
                                            <div class="analytics clr-1">
                                                <div class="analytics-icon">
                                                    <i class="las la-bullhorn"></i>
                                                </div>
                                                <div class="analytics-content no-line-braek">
                                                    <h4>{{ @$warmUp->name }}</h4>
                                                    <p>{{ $warmUp->created_at->format('d/m/Y') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-xxl-0">
                                        <div class="bg-white redious-border p-20 p-sm-30">
                                            <div class="analytics clr-2">
                                                <div class="analytics-icon">
                                                    <i class="lar la-user"></i>
                                                </div>
                                                <div class="analytics-content">
                                                    <h4>{{ !empty($totalWarmUpContact) ? $totalWarmUpContact : 'N/A' }}</h4>
                                                    <p>{{ __('total_warmup_numbers') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-6 col-md-6 mb-20 mb-lg-0">
                                        <div class="bg-white redious-border p-20 p-sm-30">
                                            <div class="analytics clr-3">
                                                <div class="analytics-icon">
                                                    <i class="las la-check-circle"></i>
                                                </div>
                                                <div class="analytics-content">
                                                    <h4>{{ !empty($totalWarmUpDeviceContact) ? $totalWarmUpDeviceContact : 'N/A' }}</h4>
                                                    <p>{{ __('total_devices') }}</p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-6 col-md-6">
                                        <div class="bg-white redious-border p-20 p-sm-30">
                                            <div class="analytics clr-4">
                                                <div class="analytics-icon">
                                                    <i class="las la-sms"></i>
                                                </div>
                                                <div class="analytics-content">
                                                    <h4>{{ !empty($totalWarmUpMessages) ? $totalWarmUpMessages : 'N/A' }}</h4>
                                                    <p>{{ __('total_messages') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col col-lg-12 col-md-10">
                    <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30">
                        <div class="row ">
                            <div class="col-lg-12">
                                <form id="filterForm">
                                    <div class="col-lg-2">
                                        <div class="mb-3">
                                            <div class="select-type-v1 list-space">
                                                <select class="form-select form-select-lg rounded-0 mb-3 without_search filterable"
                                                        aria-label=".form-select-lg example" id="status"
                                                        name="status">
                                                    <option value="">{{ __('all') }}</option>
                                                    <option value="delivered">{{ __('delivered') }}</option>
                                                    <option value="read">{{ __('read') }}</option>
                                                    <option value="failed">{{ __('failed') }}</option>
                                                    <option value="scheduled">{{ __('scheduled') }}</option>
                                                    <option value="sent">{{ __('sent') }}</option>
                                                </select>
                                                <div class="nk-block-des text-danger">
                                                    <p class="status_error error"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="default-list-table table-responsive yajra-dataTable">
                                {{ $dataTable->table() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@push('js')
    {{ $dataTable->scripts() }}
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            const advancedSearchMapping = (attribute) => {
                console.log(attribute);

                $('#dataTableBuilder').on('preXhr.dt', function (e, settings, data) {
                    data[attribute.name] = attribute.value;
                    console.log('Data being sent to server:', data);
                });
            }

            $(document).on('change', '#filterForm select', function () {
                advancedSearchMapping({
                    name: $(this).attr('name'),
                    value: $(this).val(),
                });
            });

            $(document).on('change', '#status', function (event) {
                event.preventDefault();
                console.log(event);
                $('#dataTableBuilder').DataTable().ajax.reload();
            });
        });
        const refreshDataTable = () => {
            $('#dataTableBuilder').DataTable().ajax.reload();
        }
    </script>
@endpush
