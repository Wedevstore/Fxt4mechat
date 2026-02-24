@extends('website.themes.' . active_theme() . '.master')
@section('content')
    @push('css')
    @endpush

    @include('website/themes/modern/sections/hero')
    @include('website/themes/modern/sections/partner')
    @include('website/themes/modern/sections/story')
    @include('website/themes/modern/sections/unique_feature')
    @include('website/themes/modern/sections/feature')
    @include('website/themes/modern/sections/advantage')
    @include('website/themes/modern/sections/flow-builder')
    @include('website/themes/modern/sections/highlighted-feature')

    @include('website.themes.' . active_theme() . '.sections.pricing')

    @include('website/themes/modern/sections/faq')
    @include('website/themes/modern/sections/cta')


    @push('js')
    @endpush
@endsection
