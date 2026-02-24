@extends('backend.layouts.master')
@section('title', __('storage_setting'))
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-md-center">
            <div class="col col-lg-6 col-md-9">
                <h3 class="section-title">{{ __('storage_setting') }}</h3>
                <div class="bg-white redious-border pt-30 p-20 p-sm-30">
                    <div class="section-top">
                        <h6>{{ __('storage_setting') }}</h6>
                    </div>
                    <form action="{{ route('storage.setting') }}" method="post" class="form">@csrf
                        <div class="row gx-20">
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <label for="default_storage" class="form-label">{{ __('default_storage') }}</label>
                                    <div class="select-type-v2">
                                        <select id="default_storage" name="default_storage" class="form-select form-select-lg mb-3 without_search">
                                            <option value="local" {{ setting('default_storage') == 'local' ? 'selected' : '' }}>{{ __('local') }}</option>
                                            <option value="aws_s3" {{ setting('default_storage') == 'aws_s3' ? 'selected' : '' }}>{{ __('aws_s3') }}</option>
                                            <option value="wasabi" {{ setting('default_storage') == 'wasabi' ? 'selected' : '' }}>{{ __('wasabi') }}</option>
                                            <option value="do" {{ setting('default_storage') == 'do' ? 'selected' : '' }}>{{ __('Digital Ocean') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- End Default Storage -->

                            <div class="col-lg-6 aws_div {{ setting('default_storage') == 'aws_s3' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="awsAccessID" class="form-label">{{ __('aws_access_key_id') }}</label>
                                    <input type="text" class="form-control rounded-2" id="awsAccessID" name="aws_access_key_id"
                                           placeholder="sgd*************wdr" value="{{ isDemoMode() ? '******************' : setting('aws_access_key_id') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="aws_access_key_id_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Access Key ID -->

                            <div class="col-lg-6 aws_div {{ setting('default_storage') == 'aws_s3' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="awsAccessKey" class="form-label">{{ __('aws_access_key') }}</label>
                                    <input type="text" class="form-control rounded-2" id="awsAccessKey" name="aws_secret_access_key"
                                           placeholder="sgd*************wdr"  value="{{ isDemoMode() ? '******************' : setting('aws_secret_access_key') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="aws_secret_access_key_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Access Key -->

                            <div class="col-lg-6 aws_div {{ setting('default_storage') == 'aws_s3' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="AWSDefaultRegion"
                                           class="form-label">{{ __('aws_default_region') }}</label>
                                    <input type="text" class="form-control rounded-2" id="AWSDefaultRegion" name="aws_default_region"
                                           placeholder="ap-south-1" value="{{ isDemoMode() ? '******************' : setting('aws_default_region') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="aws_default_region_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Default Region -->

                            <div class="col-lg-6 aws_div {{ setting('default_storage') == 'aws_s3' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="AWSBucket" class="form-label">{{ __('aws_bucket') }}</label>
                                    <input type="text" class="form-control rounded-2" id="AWSBucket" name="aws_bucket"
                                           placeholder="demo123456" value="{{ isDemoMode() ? '******************' :  setting('aws_bucket') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="aws_bucket_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Bucket -->
                            <div class="col-lg-6 do_div {{ setting('default_storage') == 'do' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="doAccessID" class="form-label">{{ __('access_key_id') }}</label>
                                    <input type="text" class="form-control rounded-2" id="doAccessID" name="do_access_key_id"
                                           placeholder="sgd*************wdr" value="{{ isDemoMode() ? '******************' : setting('do_access_key_id') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="do_access_key_id_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Access Key ID -->

                            <div class="col-lg-6 do_div {{ setting('default_storage') == 'do' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="doAccessKey" class="form-label">{{ __('access_key') }}</label>
                                    <input type="text" class="form-control rounded-2" id="doAccessKey" name="do_secret_access_key"
                                           placeholder="sgd*************wdr"  value="{{ isDemoMode() ? '******************' : setting('do_secret_access_key') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="do_secret_access_key_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Access Key -->

                            <div class="col-lg-6 do_div {{ setting('default_storage') == 'do' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="doDefaultRegion"
                                           class="form-label">{{ __('default_region') }}</label>
                                    <input type="text" class="form-control rounded-2" id="doDefaultRegion" name="do_default_region"
                                           placeholder="ap-south-1" value="{{ isDemoMode() ? '******************' : setting('do_default_region') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="do_default_region_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End AWS Default Region -->

                            <div class="col-lg-6 do_div {{ setting('default_storage') == 'do' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="doBucket" class="form-label">{{ __('bucket') }}</label>
                                    <input type="text" class="form-control rounded-2" id="doBucket" name="do_bucket"
                                           placeholder="demo123456" value="{{ isDemoMode() ? '******************' :  setting('do_bucket') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="do_bucket_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 do_div {{ setting('default_storage') == 'do' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="do_directory" class="form-label">{{ __('directory') }}</label>
                                    <input type="text" class="form-control rounded-2" id="do_directory" name="do_directory"
                                           placeholder="salebot" value="{{ isDemoMode() ? '******************' :  setting('do_directory') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="do_directory_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End DO -->
                            <div class="col-lg-6 wasabi_div {{ setting('default_storage') == 'wasabi' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="wasabiAccessID"
                                           class="form-label">{{ __('wasabi_access_key_id') }}</label>
                                    <input type="text" class="form-control rounded-2" id="wasabiAccessID"
                                           placeholder="sgd*************wdr" name="wasabi_access_key_id" value="{{ isDemoMode() ? '******************' : setting('wasabi_access_key_id') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="wasabi_access_key_id_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Wasabi Access Key ID -->

                            <div class="col-lg-6 wasabi_div {{ setting('default_storage') == 'wasabi' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="wasabiSecretAccesskey"
                                           class="form-label">{{ __('wasabi_secret_access_key') }}</label>
                                    <input type="text" class="form-control rounded-2" id="wasabiSecretAccesskey"
                                           placeholder="sgd*************wdr" name="wasabi_secret_access_key"  value="{{ isDemoMode() ? '******************' : setting('wasabi_secret_access_key') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="wasabi_secret_access_key_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Wasabi secret access key -->

                            <div class="col-lg-6 wasabi_div {{ setting('default_storage') == 'wasabi' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="wasabiDefaultRegion"
                                           class="form-label">{{ __('wasabi_default_region') }}</label>
                                    <input type="text" class="form-control rounded-2" id="wasabiDefaultRegion"
                                           placeholder="ap-south-1" name="wasabi_default_region" value="{{ isDemoMode() ? '******************' : setting('wasabi_default_region') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="wasabi_default_region_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Wasabi Default Region -->

                            <div class="col-lg-6 wasabi_div {{ setting('default_storage') == 'wasabi' ? '' : 'd-none' }}">
                                <div class="mb-4">
                                    <label for="wasabiBucket" class="form-label">{{ __('wasabi_bucket') }}</label>
                                    <input type="text" class="form-control rounded-2" id="wasabiBucket"
                                           placeholder="demo123456" name="wasabi_bucket" value="{{ isDemoMode() ? '******************' : setting('wasabi_bucket') }}">
                                    <div class="nk-block-des text-danger">
                                        <p class="wasabi_bucket_error error"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Wasabi Bucket -->
                        </div>
                        <div class="d-flex justify-content-start align-items-center">
                            <button type="submit" class="btn sg-btn-primary">{{ __('update') }}</button>
                            @include('backend.common.loading-btn',['class' => 'btn sg-btn-primary'])
                        </div>
                    </form>
                    <h6 class="sub-title mt-40">{{ __('image_optimization') }}</h6>
                    <div class="d-flex justify-content-between mb-40">
                        <label for="checkbox1">{{ __('available_image_optimization') }}</label>
                        <div class="setting-check">
                            <input type="checkbox" id="checkbox1" data-field_for="image_optimization" name="image_optimization" value="setting-status-change/image_optimization" class="status-change"
                                {{ setting('image_optimization') == 1 ? 'checked' : '' }}>
                            <label for="checkbox1"></label>
                        </div>
                    </div>
                    <form action="{{ route('storage.setting') }}" method="post"  class="form">@csrf
                        <div class="row">
                            <!-- End Image Optimisation Checkbox -->
                            <div class="col-lg-12 optimization_div {{ setting('image_optimization') == 1 ? '' : 'd-none' }}">
                                <div class="d-flex justify-content-between mb-40">
                                    <label for="optimisationPercentage"
                                           class="form-label">{{ __('optimisation_percentage') }}</label>
                                    <div class="w-25">
                                        <input type="number" class="form-control rounded-2"
                                               id="optimisationPercentage" placeholder="80" name="image_optimization_percentage" value="{{ setting('image_optimization_percentage') }}">
                                        <div class="nk-block-des text-danger">
                                            <p class="image_optimization_percentage_error error"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Optimisation Percentage -->

                            <div class="d-flex justify-content-start align-items-center">
                                <button type="submit" class="btn sg-btn-primary">{{ __('update') }}</button>
                                @include('backend.common.loading-btn',['class' => 'btn sg-btn-primary'])
                            </div>
                        </div>
                    </form>
                    <!-- End Image Optimization -->
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            $(document).ready(function () {
                $(document).on('change', '#default_storage', function () {
                    var storage = $(this).val();
                    $('.aws_div').addClass('d-none');
                    $('.do_div').addClass('d-none');
                    $('.wasabi_div').addClass('d-none');
                    if (storage == 'aws_s3') {
                        $('.aws_div').removeClass('d-none');
                    } else if (storage == 'do' ) {
                        $('.do_div').removeClass('d-none');
                    } else if (storage == 'wasabi') {
                        $('.wasabi_div').removeClass('d-none');
                    }
                });
            });
        });
    </script>
@endpush
