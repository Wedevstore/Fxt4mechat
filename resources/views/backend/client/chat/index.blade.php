@extends('backend.layouts.master')
@section('title', __('inbox'))
@push('css')
    <link rel="stylesheet" href="{{ static_asset('client/css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ static_asset('client/css/vue-plyr.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ static_asset('client/css/emoji.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/template.css') }}?v={{ time() }}">
<style>
  .MessageField__MessageContainer {
      padding-right: 370px;
  }

  @media (max-width: 1339px) {
      .chat__sendArea .new-chat-form .dropDown__icon {
          display: flex;
      }

      .chat__sendArea .new-chat-form .bottom-icons {
          background: #fff;
          padding: 10px 15px;
          top: 0;
          transform: initial;
          opacity: 0;
          visibility: hidden;
          transition: all 300ms ease-in-out;
          z-index: -1;
      }
      .chat__sendArea .new-chat-form .bottom-icons.active {
          top: -55px;
          opacity: 1;
          visibility: visible;
          z-index: 1;
      }
      .chat__sendArea .new-chat-form textarea {
          padding: 13px;
          padding-right: 45px;
          /* height: 50px; */
          width: calc(100% - 55px);
      }
      .chat__sendArea .new-chat-form .form-icon.icon-send {
          /* width: 48px;
          height: 48px; */
      }
    .MessageField__MessageContainer {
        padding-right: 50px;

    }
  }

  @media (max-width: 479px) {
    .sp-static-bar .bottom-icons {
        gap: 8px;
    }
    .sp-static-bar .bottom-icons button {
        font-size: 20px;
    }
  }


  .modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
  }
  .modal-wrapper {
    margin: 0 15px;
  }
  .modal-container {
    width: 100%;
    max-width: 530px;
    /* margin: 150px auto; */
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);

    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  .col-10.shopifyModel {
      padding-left: 17px;
  }
  .productImage {
      width: 80px;
  }
  .modal-mask .modal-body {
      padding: 0;
      padding-bottom: 0px;
      border: 0;
      margin-top: 0px;
  }
  .shopifyModel ul li{
    float: left;
    padding: 1px;
    color: white;
  }

  .shopifyModel ul li a {
      font-size: 16px;
      background: #354ABF;
      border-radius: 3px;
      color: white;
      padding: 5px 8px;
      font-weight: 500;
      font-size: 12px;
  }
  .title{
    font-size: 14px;
    font-weight: 600;
  }
  @media (max-width: 575px) {
    .modal-container {
      padding: 20px;

    }
  }

  .loadingButton {
    background-color: #d3d3d3!important;
    pointer-events: none;
    cursor: not-allowed;
  }
  .modal-header{
    border-bottom: 1px solid #e9e9e9;
    margin-bottom: 10px;
    font-size: 16px;
    font-weight: 500;
    line-height: 19px;
    color: #556068;
    padding-bottom: 10px;
  }
  .modal-body{
    /* max-height: 500px; */
    overflow-x: hidden;
    overflow-y: auto;
    width: 100%;
    max-height: 380px;
  }
  .btn-green {
    color: white !important;
    background-color: green !important;
    border-radius: 5px;
    padding: 6px 10px;
    display: inline-block;
  }
  @media (max-width: 992px) {
      .chat-customtoggle-phone {
        width: 100% !important;
        margin-top: 10px;
      }
    }

  /* .message-status i {
      font-size: 13px;
      margin-left: 4px;
  }

  i.la.la-check-double.not-seen {
      color: #9e9e9e !important;
      padding-left: 10px;
  }

  i.la.la-check-double.seen {
      color: green !important;
      padding-left: 10px;
  } */


</style>
@endpush
@php
    $manifest_file = public_path('client/js/build/manifest.json');
    $manifest = file_exists($manifest_file) ? json_decode(file_get_contents($manifest_file), true) : ['resources/js/app.js' => ['file' => 'app.js']];
    $js = $manifest['resources/js/app.js']['file'];
    $parse_url = parse_url(config('app.url'));
    $path = '/';

    if(!empty($parse_url['path'])){
        $path = trim($parse_url['path'], '/');
    }
@endphp
@section('content')
    <div id="app"></div>
    <input type="hidden" value="{{ url('/') }}" id="base_url">
    <input type="hidden" value="{{ static_asset('/') }}" id="asset_url">
    <input type="hidden" value="{{ setting('is_pusher_notification_active') }}" id="is_pusher_active">
    <input type="hidden" value="{{ setting('pusher_app_key') }}" id="f_pusher_app_key">
    <input type="hidden" value="{{ setting('pusher_app_cluster') }}" id="f_pusher_app_cluster">
    <input type="hidden" value="{{ json_encode(auth()->user()->client) }}" id="auth_user">
    <input type="hidden" value="{{ json_encode($contact) }}" id="contact">
    <input type="hidden" id="app_path" value="{{ $path }}">
@endsection
@push('js')
<script>
    // window.translations = {!! json_encode(json_decode(file_get_contents(base_path('lang/en.json')), true)) !!};
  <?php 
  // Determine the locale, defaulting to 'en' if not set
  $locale = systemLanguage() ? systemLanguage()->locale : 'en';
  // Path to the language file
  $langFilePath = base_path("lang/{$locale}.json");
  // Load the language file's contents
  $translations = file_exists($langFilePath) ? json_decode(file_get_contents($langFilePath), true) : [];
  // Encode the translations to JSON and pass to JavaScript
  ?>
  window.translations = {!! json_encode($translations) !!};
  </script>
    @if(file_exists($manifest_file))
        {{ Vite::useBuildDirectory('client/js/build') }}
        <script src="{{ static_asset("client/js/build/$js") }}?v={{ time() }}"></script>
    @else
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    @endif
@endpush
