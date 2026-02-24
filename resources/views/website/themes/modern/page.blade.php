@extends('website.themes.'.active_theme().'.master')

@section('content')
    <div class="cns-banner-area banner-style-1">
    </div>
    <section class="feature__section bg-color py-100 py-sm-60">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb--0">
                        <h5 class="title mb--32 mt-2" style="padding: 10px 0px; font-size:24px">{!! $page_info->title !!}</h5>
                    </div>
                </div>
                <div class="col-lg-12">
                    {!! $page_info->content !!}
                </div>
            </div>
        </div>
    </div>
@endsection